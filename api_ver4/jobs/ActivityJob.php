<?php

namespace api_ver4\jobs;

use yii\base\Object;
use yii\queue\Job;

class ActivityJob extends Object implements Job
{
    public $command;

    public function execute($queue)
    {
        $this->command->queryAll();
    }

}