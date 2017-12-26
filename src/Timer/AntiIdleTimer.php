<?php
/**
 * @author    Xueron Ni <xueron@xueron.com>
 * @copyright 2017
 *
 * @see       https://www.github.com/xueron
 */
namespace Xueron\FastDPhalcon\Timer;

use FastD\Swoole\Timer;
use Monolog\Logger;

class AntiIdleTimer extends Timer
{
    public function handle($id, array $params = [])
    {
        $pid = getmypid();
        $databasePool = $params[0];
        $maxRetry = $params[1];
        $time = microtime(1);
        foreach ($databasePool->getConfig() as $key => $config) {
            $tryTimes = 1;
            while ($tryTimes < $maxRetry) {
                try {
                    $info = $databasePool->getConnectionInfo($key);
                    logger()->log(Logger::DEBUG, "[$pid] [Database $key] [$time] AntiIdle: " . $info['server']);
                    break;
                } catch (\Exception $e) {
                    if (preg_match("/(errno=32 Broken pipe)|(MySQL server has gone away)/", $e->getMessage())) {
                        logger()->log(Logger::ERROR, "[$pid] [Database $key] Connection lost({$e->getMessage()}), try to reconnect, tried times $tryTimes");
                        $databasePool->reconnect($key);
                        $tryTimes ++;
                        continue;
                    }
                    logger()->log(Logger::ERROR, "[$pid] [Database $key] Quit on exception: " . $e->getMessage());
                    exit(255);
                }
            }
        }
    }
}
