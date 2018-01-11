<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

class TestController extends Controller
{
    public function actionTest()
    {
        Yii::$app->queue->push(new TestJob([
            'name' => 'Test',
        ]));
    }
}