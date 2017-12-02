# FastD Phalcon
在 FastD 框架中使用 Phalcon 的 ORM, 替换 FastD 自带的 Medoo。

FastD是基于swoole的微服务框架。
Phalcon是一个用C编写的PHP框架，其ORM性能相当好。请阅读了解如何[安装Phalcon](https://docs.phalconphp.com/en/3.2/installation)？

## 安装
```bash
$ composer require xueron/fastd-phalcon
```

## 配置
修改配置文件 `config/app.php`，添加 `PhalconServiceProvider`，如下：
```php
<?php
return [
    // 省略了无关配置
    'services' => [
        \Xueron\FastDPhalcon\PhalconServiceProvider::class,
    ],
];
```

## 其他配置
可选在 config.php 中增加如下配置：
```php
<?php
return [
    // 省略了其他配置
    'phalcon' => [
        'debug' => false, // 开启后，会DEBUG日志打印数据库调试日志。DEBUG日志在app.php中配置
        'antiidle' => false, // 开启后，会通过定时器定时访问一下数据库，方式发呆断线
        'interval' => 100, // 防发呆定时间隔，单位秒，建议比mysql的wait_timeout略短
        'maxretry' => 3, // 出现断线，自动重连的尝试次数，尝试多次不成功，worker会退出
    ]
];
```

## 配置 Model
```php
<?php
namespace Model;

use Xueron\FastDPhalcon\Model\Model;

class Subscription extends Model
{
    // 指定表名称，默认与Model类名相同
    public function getSource()
    {
        return 'subscriptions';
    }
}
```

## 分页
### 分页的简单使用
```php
<?php
use Phalcon\Paginator\Factory;

$builder = phalcon_builder()
                ->columns('id, name')
                ->from('Robots')
                ->orderBy('name');

$options = [
    'builder' => $builder,
    'limit'   => 20,
    'page'    => 1,
    'adapter' => 'queryBuilder',
];

$paginator = Factory::load($options);
```
或者

```php
<?php
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

$builder = phalcon_builder()
                ->columns('id, name')
                ->from('Robots')
                ->orderBy('name');
                
$paginator = new PaginatorQueryBuilder(
    [
        'builder' => $builder,
        'limit'   => 20,
        'page'    => 1,
    ]
);
```


## 其他资源
如果你对 Phalcon 感兴趣，请阅读[官方文档](docs.phalconphp.com/en/latest/index.html)。

如果你想快速使用 Phalcon，可以尝试这个开箱即用的[Pails框架](https://github.com/xueron/pails)。

如果你对Phalcon的DB和ORM不熟悉，请阅读下面的资料。
* [Phalcon - Database: Layer](https://docs.phalconphp.com/en/3.2/db-layer)
* [Phalcon - Database: Models](https://docs.phalconphp.com/en/3.2/db-models)
* [Phalcon - Database: Pagination](https://docs.phalconphp.com/en/3.2/db-pagination)
* [Phalcon - Database: PHQL](https://docs.phalconphp.com/en/3.2/db-phql)
