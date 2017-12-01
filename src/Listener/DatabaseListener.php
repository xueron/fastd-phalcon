<?php
/**
 * DatabaseListener.php
 *
 */

namespace Xueron\FastDPhalcon\Listener;

use Monolog\Logger;
use Phalcon\Db\Profiler;
use Phalcon\Events\Event;

/**
 * Class DatabaseListener
 *
 * @package Xueron\FastDPhalcon\Listener
 */
class DatabaseListener
{
    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Phalcon\Db\Profiler
     */
    protected $profiler;

    /**
     * Creates the profiler and starts the logging
     */
    public function __construct()
    {
        $this->profiler = new Profiler();
        $this->logger   = logger();
    }

    /**
     * This is executed if the event triggered is 'beforeQuery'
     */
    public function beforeQuery(Event $event, $connection)
    {
        $this->profiler->startProfile(
            $connection->getSQLStatement(), $connection->getSQLVariables()
        );
    }

    /**
     * This is executed if the event triggered is 'afterQuery'
     */
    public function afterQuery(Event $event, $connection)
    {
        $this->profiler->stopProfile();

        $profile = $this->profiler->getLastProfile();
        $sql   = $profile->getSQLStatement();
        $vars  = $profile->getSQLVariables();
        if (count($vars)) {
            $sql = str_replace(array_map(function ($v) {
                return ':' . $v;
            }, array_keys($vars)), array_values($vars), $sql);
        }

        $start = $profile->getInitialTime();
        $final = $profile->getFinalTime();
        $total = $profile->getTotalElapsedSeconds();
        $this->logger->log(Logger::DEBUG, "[Database]: Start=$start, Final=$final, Total=$total, SQL=$sql");
    }

    /**
     * @return \Phalcon\Db\Profiler
     */
    public function getProfiler()
    {
        return $this->profiler;
    }
}
