<?php

namespace Lib;
/**
 * Description of WsAgent
 *
 */
class Getini{

    public function get_config($configfilename)
    {
        $debug=0;

        #创建空的配置栈
        $configs=array();

        $rows=@file($configfilename); #逐行读取记录
        foreach($rows as $row)
        {
            #清理空白字符
            $config=trim($row);
            #过滤掉空行；处理为空行
            if ($config)
            {
                #过滤注释行；处理非注释行
                if(substr($config,0,1)<>"#")
                {
                    #删除行尾注释
                    if ($pos==strpos($config,"#",0)) {$config=substr($config,0,$pos);}
                    #根据“=”分割配置项和配置值，并进行格式化处理
                    if ($pos==strpos($config,"=",0))
                    {
                        #获取key：配置项
                        $key=strtolower(trim(substr($config,0,$pos)));
                        #获取value：配置值
                        $value=trim(preg_replace('/[ ]{1,}/'," ",substr($config,$pos+1)));
                        #将配置入栈，等待返回
                        $configs["$key"]=$value;
                        #debug模式，打印调式信息
                        if ($debug) {print "\t$key:$value\n";}
                    }
                }
            }
        }

        return $configs;
    }



}
