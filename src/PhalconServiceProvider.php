<?php
/**
 * PhalconServiceProvider.php
 *
 */

namespace Xueron\FastDPhalcon;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
use Phalcon\Crypt;
use Phalcon\Di;
use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Escaper;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Filter;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData\Memory as MemoryMetadata;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Security;
use Xueron\FastDPhalcon\Console\ModelConsole;
use Xueron\FastDPhalcon\Pool\DatabasePool;

/**
 * Class PhalconServiceProvider
 *
 * @package Xueron\FastDPhalcon
 */
class PhalconServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \FastD\Container\Container $container
     *
     * @return mixed|void
     */
    public function register(Container $container)
    {
        // 注入Phalcon的容器
        $container->add('phalcon', $this->getDi());

        // 注入PhalconDB服务
        $config = config()->get('database', []);
        $container->add('phalcon_db', new DatabasePool($config));
        unset($config);

        // 注册命令行工具
        config()->merge([
            'consoles' => [
                ModelConsole::class,
            ],
        ]);
    }

    /**
     * Init a Phalcon Di object
     *
     * @return \Phalcon\Di
     */
    protected function getDi()
    {
        $di = new Di();
        $di->setShared('annotations', Memory::class);
        $di->setShared('modelsManager', Manager::class);
        $di->setShared('modelsMetadata', MemoryMetadata::class);
        $di->setShared('eventsManager', EventsManager::class);
        $di->setShared('transactionManager', TransactionManager::class);
        $di->setShared('filter', Filter::class);
        $di->setShared('escaper', Escaper::class);
        $di->setShared('security', Security::class);
        $di->setShared('crypt', Crypt::class);

        return $di;
    }
}
