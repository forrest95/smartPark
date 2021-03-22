<?php

namespace Lib;
/**
 * Created by PhpStorm.
 * User: yaohuif
 * Date: 2020/5/5
 * Time: 11:38
 */

class File{

    //判断目录是否为空
    public static function is_empty_dir($fp)
    {
        //如果目录不存在，为空
        if (!is_dir($fp)) {
            //不是一个目录 目录不存在
            return true;
        }

        $H = @opendir($fp);
        $i=0;
        while($_file=readdir($H)){
            $i++;
        }
        closedir($H);
        if($i>2){
//            return "不为空";
            return false;
        }else{
//            return "为空";
            return true;
        }
    }

    

    public static function searchDir($path,&$files){

        if(is_dir($path)){

            $opendir = opendir($path);

            while ($file = readdir($opendir)){
                if($file != '.' && $file != '..'){
                    self::searchDir($path.'/'.$file, $files);
                }
            }
            closedir($opendir);
        }
        if(!is_dir($path)){
            $files[] = $path;
        }
    }

    //得到目录名
    public static function getDir($dir){
        $files = array();

        self::searchDir($dir, $files);
        return $files;
    }


    //-----------------------------------------下面来源于FileUtil
    /**
     * 移动文件
     *
     * @param string $fileUrl
     * @param string $aimUrl
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     * @return boolean
     */
    public static function moveFile($fileUrl, $aimUrl, $overWrite = false) {
        if (!file_exists($fileUrl)) {
            return false;
        }
        if (file_exists($aimUrl) && $overWrite = false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite = true) {
            self::unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        self::createDir($aimDir);
        rename($fileUrl, $aimUrl);
        return true;
    }

    /**
     * 建立文件夹
     *
     * @param string $aimUrl
     * @return viod
     */
    public static function createDir($aimUrl) {
        $aimUrl = str_replace('', '/', $aimUrl);
        $aimDir = '';
        $arr = explode('/', $aimUrl);
        $result = true;
        foreach ($arr as $str) {
            $aimDir .= $str . '/';
            if (!file_exists($aimDir)) {
                $result = mkdir($aimDir);
            }
        }
        return $result;
    }

    /**
     * 删除文件
     *
     * @param string $aimUrl
     * @return boolean
     */
    public static function unlinkFile($aimUrl) {
        if (file_exists($aimUrl)) {
            unlink($aimUrl);
            return true;
        } else {
            return false;
        }
    }


    /**
     * 删除文件夹  无论是否有子文件都删除
     *
     * @param string $aimDir
     * @return boolean
     */
    public static function unlinkDir($aimDir) {
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        if (!is_dir($aimDir)) {
            return false;
        }
        $dirHandle = opendir($aimDir);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($aimDir . $file)) {
                self::unlinkFile($aimDir . $file);
            } else {
                self::unlinkDir($aimDir . $file);
            }
        }
        closedir($dirHandle);
        return rmdir($aimDir);
    }

    /** 删除所有空目录  包含文件就不删除
     * @param String $path 目录路径
     */
    public static function rm_empty_dir($path){
        if(is_dir($path) && ($handle = opendir($path))!==false){
            while(($file=readdir($handle))!==false){// 遍历文件夹
                if($file!='.' && $file!='..'){
                    $curfile = $path.'/'.$file;// 当前目录
                    if(is_dir($curfile)){// 目录
                        self::rm_empty_dir($curfile);// 如果是目录则继续遍历
                        if(count(scandir($curfile))==2){//目录为空,=2是因为.和..存在
                            rmdir($curfile);// 删除空目录
                        }
                    }
                }
            }
            closedir($handle);
        }
    }


}