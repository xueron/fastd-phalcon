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
        $workerId = server()->getSwoole()->worker_id;
        $workerType = server()->getSwoole()->taskworker ? 'TaskWorker' : 'Worker';

        $databasePool = $params[0];
        $maxRetry = $params[1];
        $time = microtime(1);
        foreach ($databasePool->getConfig() as $key => $config) {
            $tryTimes = 1;
            while ($tryTimes < $maxRetry) {
                try {
                    $info = $databasePool->getConnectionInfo($key);
                    logger()->log(Logger::DEBUG, "[$workerType $workerId] [Database $key] [$time] AntiIdle: " . $info['server']);
                    break;
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
                        logger()->log(Logger::ERROR, "[$workerType $workerId] [Database $key] Connection lost, try to reconnect, tryTimes $tryTimes");
                        $databasePool->reconnect($key);
                        $tryTimes ++;
                        continue;
                    }
                    logger()->log(Logger::ERROR, "[$workerType $workerId] [Database $key] Quit on exception: " . $e->getMessage());
                    exit;
                }
            }
        }
    }
}
