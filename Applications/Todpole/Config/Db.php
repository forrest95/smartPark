<?php
namespace Config;
/**
 * Created by PhpStorm.
 * User: yaohuif
 * Date: 2019/3/1
 * Time: 14:11
 */

class Db
{
    public static $webSocket = [
        'clientType' => 'Service', //客户端类型 App|Service
        'remote' => '127.0.0.1:8181'//websocket服务地址
    ];

    //主数据库
    public static $db1 = [
        'host'    => '211.159.215.13',//从主数据库 备份到次数据库
        'port'    => 63695,
        'user'    => 'root',
        'password' => 'hwtc',//密码
        'dbname'  => 'hw_online', //库名
        'charset'    => 'utf8',
    ];

    //次数据库
    public static $db2 = [
        'host'    => '127.0.0.1',//从主数据库 备份到次数据库
        'port'    => 3306,
        'user'    => 'root',
        'password' => 'hwtc@666',//密码
        'dbname'  => 'test',
        'charset'    => 'utf8',
    ];


}
