<?php
/**
 * @author    Xueron Ni <xueron@xueron.com>
 * @copyright 2017
 *
 * @see       https://www.github.com/xueron
 */

namespace Xueron\FastDPhalcon\Pool;

use FastD\Pool\PoolInterface;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di;
use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory as MemoryMetadata;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Xueron\FastDPhalcon\Timer\AntiIdleTimer;
use Xueron\FastDPhalcon\Listener\DatabaseListener;

/**
 * 每个工作进程独享数据库链接
 *
 * Class DatabasePool.
 */
class DatabasePool implements PoolInterface
{
    /**
     * Phalcon容器
     *
     * @var \Phalcon\Di
     */
    protected $di;

    /**
     * 数据库配置
     *
     * @var array
     */
    protected $config;

    /**
     * 定时器
     *
     * @var int
     */
    protected $timerId;

    /**
     * Database constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        // 初始化Phalcon的DI容器
        $di = new \Phalcon\Di();
        $di->setShared('annotations', Memory::class);
        $di->setShared('modelsManager', Manager::class);
        $di->setShared('modelsMetadata', MemoryMetadata::class);
        $di->setShared('eventsManager', EventsManager::class);
        $di->setShared('transactionManager', TransactionManager::class);

        $this->di = $di;
    }

    /**
     * @return \Phalcon\Di
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $key
     */
    public function reconnect($key)
    {
        $this->getConnection($key, true);
    }

    /**
     * @param      $key
     * @param bool $force
     *
     * @return \Phalcon\Db\Adapter
     */
    public function getConnection($key, $force = false)
    {
        if (!isset($this->config[$key])) {
            throw new \LogicException(sprintf('No set %s database', $key));
        }

        $serviceName = 'database.' . $key;
        if ($force || !$this->di->has($serviceName)) {
            if ($this->di->has($serviceName)) {
                // Close first
                $this->di->get($serviceName)->close();
                $this->di->remove($serviceName);
            }

            $config = $this->config[$key];
            $connection = new Mysql([
                'host'       => $config['host'],
                'port'       => $config['port'],
                'username'   => $config['user'],
                'password'   => $config['pass'],
                'dbname'     => $config['name'],
                'charset'    => isset($config['charset']) ? $config['charset'] : 'utf8',
                'persistent' => isset($config['persistent']) ? $config['persistent'] : false,
            ]);
            $connection->setEventsManager($this->di->getEventsManager());
            $this->di->set($serviceName, $connection);
        }

        return $this->di->get($serviceName);
    }

    /**
     * 获取连接的信息
     *
     * @param $key
     *
     * @return array
     */
    public function getConnectionInfo($key)
    {
        $connection = $this->getConnection($key);

        $output = [
            'server'     => 'SERVER_INFO',
            'driver'     => 'DRIVER_NAME',
            'client'     => 'CLIENT_VERSION',
            'version'    => 'SERVER_VERSION',
            'connection' => 'CONNECTION_STATUS',
        ];

        foreach ($output as $key => $value) {
            $output[$key] = @$connection->getInternalHandler()->getAttribute(constant('PDO::ATTR_' . $value));
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function initPool()
    {
        if ($this->timerId) {
            swoole_timer_clear($this->timerId);
            $this->timerId = null;
        }

        // 创建数据库连接
        foreach ($this->config as $key => $config) {
            $this->getConnection($key, true);
        }

        // 打开数据库调试日志
        if (config()->get('phalcon.debug', false)) {
            $this->di->getEventsManager()->attach('db', new DatabaseListener());
        }

        // 插入一个定时器，定时连一下数据库，防止IDEL超时断线
        if (config()->get('phalcon.antiidle', false)) {
            $interval = config()->get('phalcon.interval', 100) * 1000; // 定时器间隔
            $maxRetry = config()->get('phalcon.maxretry', 3); // 重连尝试次数
            $timer = new AntiIdleTimer($interval, [$this, $maxRetry]);
            $timer->withServer(server());
            $this->timerId = $timer->tick();
        }
    }
}
