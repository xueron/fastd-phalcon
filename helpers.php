<?php
/**
 * @param string $name
 *
 * @return \Phalcon\Db\Adapter
 */
function phalcon_db($name = 'default')
{
    return app()->get('phalcon_db')->getConnection($name);
}

/**
 * @param null $params
 *
 * @return \Phalcon\Mvc\Model\Query\Builder
 */
function phalcon_builder($params = null)
{
    return app()->get('phalcon_db')->getDi()->getModelsManager()->createBuilder($params);
}

