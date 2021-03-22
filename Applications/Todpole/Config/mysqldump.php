<?php
//如果要备份某个表，请先手动拷贝表结构到目的库
return [
    /**
     * 说明
     * key表示form database中要备份的表名
     * 请先在to database 中创建表结构
     * 该自动备份系统只备份数据，不自动创建表
     */
    'tb_logs_access'=>[
        'mysqldump'=>true, //是否执行mysqldump 开关
        'max_select'=>5000, //一次mysqldump获取最大条目
        'interval' => 3,  //执行一次mysqldump  间隔时间 单位秒
        'id' => 'id',  //标记按什么字段取数据  一般都是id
    ],

    'tb_logs_bp'=>[
        'mysqldump'=>true, //是否执行mysqldump 开关
        'max_select'=>5000, //一次mysqldump获取最大条目
        'interval' => 3,  //执行一次mysqldump  间隔时间 单位秒
        'id' => 'id',  //标记按什么字段取数据  一般都是id
    ],
    
    'tb_logs_dtc'=>[
        'mysqldump'=>true, //是否执行mysqldump 开关
        'max_select'=>5000, //一次mysqldump获取最大条目
        'interval' => 3,  //执行一次mysqldump  间隔时间 单位秒
        'id' => 'id',  //标记按什么字段取数据  一般都是id
    ]
];