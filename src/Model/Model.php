<?php
/**
 * Model.php
 *
 */

namespace Xueron\FastDPhalcon\Model;

/**
 * Class Model
 *
 * @package Xueron\FastDPhalcon\Model
 */
abstract class Model extends \Phalcon\Mvc\Model
{
    /**
     * 初始化方法，由Manager调用，且只调用一次. 在项目中定制，覆盖本方法。
     */
    public function initialize()
    {
        $this->setDatabase('default');
    }

    /**
     * 设置默认的数据库
     *
     * @param $configName
     */
    public function setDatabase($configName)
    {
        $serviceName = 'database.' . $configName;
        $this->setConnectionService($serviceName);
    }

    /**
     * 设置只读库
     *
     * @param $configName
     */
    public function setReadDatabase($configName)
    {
        $serviceName = 'database.' . $configName;
        $this->setReadConnectionService($serviceName);
    }

    /**
     * 设置读写库
     *
     * @param $configName
     */
    public function setWriteDatabase($configName)
    {
        $serviceName = 'database.' . $configName;
        $this->setWriteConnectionService($serviceName);
    }
}
