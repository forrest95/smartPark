<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 主逻辑
 * 主要是处理 onMessage onClose 三个方法
 */

use \GatewayWorker\Lib\Gateway;
use \Workerman\Lib\Timer;
use Lib\File;
use Lib\Logs;
use Config\Db as DbConfig;

class Events
{
    public static $exc_flag=0;   //flag

    /**
     * 当worker启动时触发
     * onWorkerStart为核心代码！！！
     * add by yaohuif
     */
    public static function onWorkerStart($worker){
        $config=require_once __DIR__ . '/Config/mysqldump.php'; //获取全局配置

        /*Timer::add(5, function(){
            //检查session 在这里无用！
            $sessions = Gateway::getAllClientInfo();

            foreach($sessions as $client_id =>$session) {
                if(!isset($session['visitor']) && (time()-$session['connect_time']) > 10) {
                    Gateway::closeClient($client_id);
                }
            }
        });*/

//        $db1_form = Db::instance('db1');  //实例化form 数据库
//        $db2_to = Db::instance('db2');  //实例化to 数据库

        if('PublishWorker1' == $worker->name){
            //发布者
            Logs::logInfo("start MqttWorker publish");

            $mqtt = new Workerman\Mqtt\Client('mqtt://test.mosquitto.org:1883');
            $mqtt->onConnect = function($mqtt) {
                $mqtt->publish('test007', 'hello workerman mqtt');
            };

            Timer::add(100, function()use ($mqtt){
                echo " ".PHP_EOL;
                logs::logInfo("start mqtt publish ");
                self::$exc_flag++;
                $mqtt->publish('test007', 'publish ### this is my publish info => '.self::$exc_flag);
            });

            $mqtt->connect();
        }

        if('SubscribeWorker2' == $worker->name){
            //订阅者
            Logs::logInfo("start MqttWorker subscribe ");

            $mqtt = new Workerman\Mqtt\Client('mqtt://test.mosquitto.org:1883');
            $mqtt->onConnect = function($mqtt) {
                $mqtt->subscribe('test007');
            };
            $mqtt->onMessage = function($topic, $content){
                Logs::logInfo("subscribe ### ");
                var_dump($topic, $content);
            };
            $mqtt->connect();
        }

        if('DeleteBusinessWorker1' == $worker->name){

             Logs::logInfo("start DeleteBusinessWorker");

        }


    }

    /**
     * 当客户端连上时触发
     * @param int $client_id
     */
    public static function onConnect($client_id)
    {
        $_SESSION['id'] = time();
        Gateway::sendToCurrentClient('{"type":"welcome","id":'.$_SESSION['id'].'}');
    }

   /**
    * 有消息时
    * @param int $client_id
    * @param string $message
    */
   public static function onMessage($client_id, $message)
   {
        // 获取客户端请求
        $message_data = json_decode($message, true);
        if(!$message_data)
        {
            return ;
        }

        switch($message_data['type'])
        {
            case 'login':
                break;
            // 更新用户
            case 'update':
                // 转播给所有用户
                Gateway::sendToAll(json_encode(
                    array(
                        'type'     => 'update',
                        'id'       => $_SESSION['id'],
                        'angle'    => $message_data["angle"]+0,
                        'momentum' => $message_data["momentum"]+0,
                        'x'        => $message_data["x"]+0,
                        'y'        => $message_data["y"]+0,
                        'life'     => 1,
                        'name'     => isset($message_data['name']) ? $message_data['name'] : 'Guest.'.$_SESSION['id'],
                        'authorized'  => false,
                        )
                    ));
                return;
            // 聊天
            case 'message':
                // 向大家说
                $new_message = array(
                    'type'=>'message',
                    'id'  =>$_SESSION['id'],
                    'message'=>$message_data['message'],
                );
                return Gateway::sendToAll(json_encode($new_message));
        }
   }

   /**
    * 当用户断开连接时
    * @param integer $client_id 用户id
    */
   public static function onClose($client_id)
   {
       if (isset($_SESSION['id'])) {
            // 广播 xxx 退出了
            GateWay::sendToAll(json_encode(array('type'=>'closed', 'id'=>$_SESSION['id'])));
       }
   }
}
