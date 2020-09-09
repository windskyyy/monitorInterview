<?php

/**
 * 0 8 * * * /usr/bin/hhvm /home/ubuntu/workspace/PHP/notice.php
 * 每天上午八点执行，检查当天的笔试面试情况，自动发送到邮箱里。
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once "DB.php";
require_once 'QQMailer.php';


$tablename = "userInfo";
$now       = time();

$db    = new DB();
$email = new QQMailer();

$fields = [
    'name',
];
$ret = $db->select($tablename, $fields, $conditions = [], true);

$names = [];
foreach ($ret as $arr) {
    $need    = false;
    $message = "{$arr['name']} 早上好！ 今天的笔面试的安排有： \n\n";
    $name    = "'{$arr['name']}'";
    $fields  = [
        'companyName',
        'department' ,
        'interviewStatus',
        'startTime' ,
        'endTime'  ,
    ];
    $conditions = [
        "name = {$name}",
    ];
    $res = $db->select($tablename, $fields, $conditions);
    foreach ($res as $arr) {
        $startTime = $arr['startTime'];
        $endTime   = $arr['endTime'];
        if ($startTime >= $now && $startTime - $now <= 86400) {
            $need      = true;
            $startTime = date("Y-m-d H:i", $startTime);
            $endTime   = date("Y-m-d H:i", $endTime);
            $message  .= "{$arr['companyName']}  {$arr['department']}  {$arr['interviewStatus']} $startTime ~ $endTime\n\n";
        }
    }

    if($need === true) {
        $email->send("1522972330@qq.com", "面试提醒", $message);
    }
}