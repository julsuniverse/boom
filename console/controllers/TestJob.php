<?php

namespace console\controllers;

use yii\base\Object;
//use zhuravljov\yii\queue\Job;
use yii\queue\Job;

class TestJob extends Object implements Job
{
    public $name;

    public function execute($queue)
    {
        file_put_contents(__DIR__ . '/1.txt', $this->name);
    }
}