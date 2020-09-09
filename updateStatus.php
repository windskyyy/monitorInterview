<?php

require_once "DB.php";

$userInfo = [];

echo "带*号的为必填! 其他的可用回车忽略". PHP_EOL;

echo "*请输入姓名:  ";
$userInfo['userName'] = input();
echo "*请输入公司名: ";
$userInfo['bName'] = input();
echo "请输入部门信息: ";
$userInfo['department'] = input();
echo "*请输入面试阶段: 投递，笔试，一面，二面，三面，加面，offer, dead： ";
$userInfo['interviewStatus'] = input();
echo "请输入面试阶段 processing = 0， offer = 1， dead = 2  ";
$userInfo['status'] = input();
echo "请输入开始时间: ";
$userInfo['startTime'] = input();
echo "请输入结束时间: ";
$userInfo['endTime'] = input();


$site = new Main();

$site->run($userInfo);

exit(0);

function input() {
    $fp_in = fopen("php://stdin", "r");
    $ret = trim(fgets($fp_in));
    if (empty($ret)) {
        return "";
    }
    return $ret;
}


class Main {
    private $db         = null;
    private $tablename  = 'userInfo';
    private $statuses   = ["'投递'","'笔试'","'一面'","'二面'","'三面'","'加面'","'offer'","'dead'"];

    function __construct() {
        $this->db = new DB();
        date_default_timezone_set("Asia/Shanghai");
    }

    /**
     * @param $userInfo
     */
    public function run($userInfo) {
        // 参数校验
        $userInfo['userName']        = strval($userInfo['userName']);
        $userInfo['userName']        = "'{$userInfo['userName']}'";

        $userInfo['bName']           = strval($userInfo['bName']);
        $userInfo['bName']           = "'{$userInfo['bName']}'";

        $userInfo['interviewStatus'] = strval($userInfo['interviewStatus']);
        $userInfo['interviewStatus'] = "'{$userInfo['interviewStatus']}'";

        $userInfo['status']          = intval($userInfo['status']);
        $userInfo['startTime']       = intval($userInfo['startTime']);
        $userInfo['endTime']         = intval($userInfo['endTime']);

        if (!$this->judge($userInfo)) {
            echo "参数错误\n";
            return ;
        }

        if ($userInfo['startTime'] != "") {
            // 202009071930
            $userInfo['startTime'] = strtotime($userInfo['startTime']);
            if ($userInfo['endTime'] != "") {
                $userInfo['endTime'] = strtotime($userInfo['endTime']);
            } else {
                $userInfo['endTime'] = $userInfo['startTime'] + 3600;
            }
        }

        // 根据是否第一次插入这一条记录，是则为插入数据，否则是更新数据
        $first = $this->isFirst($userInfo);

        if($first === -1) {
            echo "数据库查询为空\n";
            return ;
        }

        $funcName = "updateInfo";
        if ($first > 0) {
            if ($first == 1) {
                echo "*第一次输入，请输入邮箱: ";
                $userInfo['email'] = input();
                if (empty($userInfo['email'])) {
                    echo "email param error".PHP_EOL;
                    return ;
                }
                $userInfo['email'] = strval($userInfo['email']);
                $userInfo['email'] = "'{$userInfo['email']}'";
            }
            $funcName = "insertInfo";
        }
        $this->$funcName($userInfo);
    }

    /**
     * @param $userInfo
     * @return bool|int     -1 表示 数据查询为空
     */
    private function isFirst($userInfo) {
        $fields = [
            'count(1)',
        ];
        $conditions = [
            "name = {$userInfo['userName']}",
        ];
        $ret = $this->db->select($this->tablename, $fields, $conditions);
        if ($ret === false) {
            return -1;
        }
        if ($ret[0]['count(1)'] == 0) {
            return 1;
        }
        $conditions = [
            "name = {$userInfo['userName']}",
            "companyName = {$userInfo['bName']}",
        ];
        $ret = $this->db->select($this->tablename, $fields, $conditions);
        if ($ret[0]['count(1)'] == 0) {
            return 2;
        }
        return 0;
    }

    /**
     * @param $userInfo
     * @return bool
     */
    private function judge($userInfo) {
        if (!isset($userInfo['userName']) || !isset($userInfo['bName']) || !isset($userInfo['interviewStatus'])) {
            echo "userName or bName or interviewStatus error\n";
            return false;
        }

        if ($userInfo['interviewStatus'] != "" && !in_array($userInfo['interviewStatus'], $this->statuses)) {
            echo "interviewStatus error\n";
            return false;
        }

        if ($userInfo['status'] < 0 || $userInfo['status'] > 2) {
            echo "status error\n";
            return false;
        }

        return true;
    }

    private function insertInfo($userInfo) {
        $params = [
            'name'            => $userInfo['userName'],
            'companyName'     => $userInfo['bName'],
            'interviewStatus' => $userInfo['interviewStatus'],
            'status'          => $userInfo['status'],
            'startTime'       => $userInfo['startTime'],
            'endTime'         => $userInfo['endTime'],
            'email'           => $userInfo['email'],
        ];
        return $this->db->insert($this->tablename,$params);
    }

    private function updateInfo($userInfo) {
        $params = [
            'interviewStatus' => $userInfo['interviewStatus'],
            'status'          => $userInfo['status'],
            'startTime'       => $userInfo['startTime'],
            'endTime'         => $userInfo['endTime'],
        ];
        $conditions = [
            " name = {$userInfo['userName']} ",
            " companyName = {$userInfo['bName']} ",
        ];
        return $this->db->update($this->tablename, $params, $conditions);
    }

}


