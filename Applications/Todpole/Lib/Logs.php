<?php

namespace Lib;
use GatewayWorker\Lib\Db;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Logs {
    //日志目录
    private static $_logtypes=array(
        'global_enabled' => true, //全局开启or关闭日志功能
        'types'=>[
            'emerg' => ['num'=>0,'enabled'=>true],
            'fatal' => ['num'=>0,'enabled'=>true],
            'alert' => ['num'=>100,'enabled'=>true],
            'crit'  => ['num'=>200,'enabled'=>true],
            'error' => ['num'=>300,'enabled'=>true],
            'warn'  => ['num'=>400,'enabled'=>true],
            'notice'=> ['num'=>500,'enabled'=>true],
            'info'  => ['num'=>600,'enabled'=>true],
            'debug' => ['num'=>700,'enabled'=>true]
        ]
    );

    public static function setLog($priority, $log) {
        $_logtypes=self::$_logtypes;

        if($_logtypes['global_enabled']==true && $_logtypes['types'][$priority]['enabled']==true ){
            //开启了日志 才进行记录log
            $type_num=$_logtypes['types'][$priority]['num'];  //取得每个type对应的数字
            $now = time();
            echo date("m-d H:i:s", $now) . " : {$log}" . PHP_EOL;
            Db::instance('db2')->insert('tb_logs')->cols(array('logtype', 'logtext', 'logtime'))->bindValues(array('logtype' => $type_num, 'logtext' => $log, 'logtime' => $now))->query();
        }

    }

    public static function logNotice($log) {
        self::setLog('notice', $log);
    }

    public static function logDebug($log){
        self::setLog('debug', $log);
    }

    public static function logError($log) {
        self::setLog('error', $log);
    }

    public static function logInfo($log) {
        self::setLog('info', $log);
    }

    public static function logWarn($log) {
        self::setLog('warn', $log);
    }



}