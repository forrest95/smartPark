<?php
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use WorkerF\App;

// echo error message on daemon mode
Worker::$stdoutFile = './tmp/log/error.log';
Worker::$logFile = './tmp/log/workerman.log';

$http_worker = new Worker('http://0.0.0.0:8055');
$http_worker->count = 4;
$http_worker->user = 'www-data';
$http_worker->name='workerA';

$http_worker->onWorkerStart = function($http_worker) {
    // bootstrap
    require_once __DIR__ . '/bootstrap/boot.php';
    // init app
    App::init();
};

$http_worker->onMessage = function($con, $data) {
    // run web app
    App::run($con);
};

Worker::runAll();
