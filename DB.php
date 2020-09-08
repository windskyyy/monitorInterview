<?php

class DB {
    public $sql         = "";
    private $mysql_conf = [];

    public function __construct () {
        $this->mysql_conf = array(
            'host'    => 'cdb-027nnpt2.cd.tencentcdb.com',
            'db'      => 'List',
            'db_user' => 'root',
            'db_pwd'  => 'Xtm_0124',
        );
        $this->mysqli = @new mysqli($this->mysql_conf['host'], $this->mysql_conf['db_user'],
            $this->mysql_conf['db_pwd'], 'List', 10106);
        $this->mysqli->query("set names 'utf8';");//编码转化
    }

    function __destruct() {
        $this->pdo = null;
    }

    // insert into [tablename] (fields, ...) values ($values, ...)
    public function insert ($tableName, $params) {
        $sql = "insert into $tableName( ";
        $first = 0;
        foreach ($params as $field => $value) {
            if ($first) {
                $sql .= ",";
            }
            $sql .= " $field ";
            $first = 1;
        }
        $sql .= ") values ( ";
        $first = 0;
        foreach ($params as $value) {
            if ($first) {
                $sql .= ",";
            }
            $sql .= $value;
            $first = 1;
        }
        $sql .= ' ) ';
        return $this->getDbRes($sql);
    }

    // delete from [tablename] where [clause...]
    public function delete ($tableName, $conditions) {
        $sql = "delete from $tableName ";
        $first = 1;
        foreach ($conditions as $condition) {
            if ($first) {
                $sql .= " where ";
            } else {
                $sql .= " and ";
            }
            $sql .= $condition;
            $first = 0;
        }
        return $this->getDbRes($sql);
    }

    // update [tablename] set [... = ...], [... = ...] where [conditions]";
    public function update($tableName, $params, $conditions) {
        $sql = "update $tableName set ";
        $first = 1;
        foreach ($params as $key => $val) {
            if (!$first) {
                $sql .= ' , ';
            }
            $sql .= " $key = $val ";
            $first = 0;
        }
        $first = 1;
        foreach ($conditions as $condition) {
            if ($first) {
                $sql .= " where ";
            } else {
                $sql .= " and ";
            }
            $sql .= " $condition ";
            $first = 0;
        }
        return $this->getDbRes($sql);
    }

    // select [fields] from [tablename] where [condition and condition] group by [groups] order by [seq] limit [number];
    public function select($tablename, $fields, $conditions) {
        $sql = "select ";
        $first = 1;
        foreach ($fields as $field) {
            if (!$first) {
                $sql .= ' , ';
            }
            $sql .= $field;
            $first = 0;
        }
        $sql .= " from $tablename where ";
        $first = 1;
        foreach ($conditions as $condition) {
            if (!$first) {
                $sql .= ' and ';
            }
            $sql .= $condition;
            $first = 0;
        }
        return $this->getDbRes($sql, true);
    }

    /**
     * @param $sql
     * @return array|bool
     *
     * 返回值为空返回false
     */
    public function getDbRes($sql, $isSelect = false) {
        $res = $this->mysqli->query($sql);
        if (!$res) {
            die("sql error: \n $sql\n" . $this->mysqli->error);
        }
        if ($isSelect === false) {
            return $res;
        }
        $ret = [];
        while ($row = $res->fetch_assoc()) {
            $ret[] = $row;
        }
        if (empty($ret)) {
            return false;
        }
        return $ret;
    }

}