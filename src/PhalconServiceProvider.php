<?php
/**
 * PhalconServiceProvider.php
 *
 */
namespace Xueron\FastDPhalcon;

use FastD\Container\Container;
use FastD\Container\ServiceProviderInterface;
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
        // 注入PhalconDB服务
        $config = config()->get('database', []);
        $container->add('phalcon_db', new DatabasePool($config));
        unset($config);

        // 注册命令行工具
        config()->merge([
            'consoles' => [
                ModelConsole::class
            ]
        ]);
    }
}
