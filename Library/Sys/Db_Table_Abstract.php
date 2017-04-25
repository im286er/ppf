<?php

/**
 *  数据库表抽象类 Db_Table_Abstract
 *  主要是pdo_mysql的封装
 *  以及实现$select = $this->db->select()->from()->where()->orderby()->limit();这类的查询
 *
 */

class Db_Table_Abstract
{
    public $select;
    protected $table_name;
    private $strDsn;
    public $DbConnect;
    protected static $getInstance;

    public $DbSqlArr = array();

    private function __CONSTRUCT() {
        include APPLICATION_PATH . "/Config.php";
        try {
            //mssql 需要用dblib
            // 连接数据库的字符串定义
            $this->strDsn = "mysql:host=" . $database_config['host'] . ";dbname=" . $database_config['dbname'];
            $db = new PDO($this->strDsn, $database_config['username'], $database_config['password']);
            $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $db->query("set names " . $database_config['charset']);
            $this->DbConnect = $db;
        } catch (PDOException $e) {
            var_dump($e->getMessage());
            die;
        }
    }

    public static function Db_init() {
        if (self::$getInstance === null) {
            self::$getInstance = new self();
        }
        return self::$getInstance;
    }

    public function select($field = "*") {
        $field_arr = explode(",", $field);
        foreach ($field_arr as $key => $val) {
            $new_field .= "'" . $val . "',";
        }
        $new_field = substr($new_field, 0, strlen($new_field) - 1);
        $sql = "select " . $new_field;
        $this->DbSqlArr['_select'] = $sql;
        return $this->DbSqlArr;
    }

    public function from($table_name = "") {
        if (empty($table_name)) {
            $mysql_error = "tableName can  not be null";
            return $mysql_error;
            die;
        } else {
            $this->DbSqlArr['_from'] = " from " . $table_name;
            return $this->DbSqlArr;
        }
    }

    public function where($where_list = "") {
        $this->DbSqlArr['_where'] = " " . $where_list;
        return $this->DbSqlArr;
    }

    public function orderby($field = "id", $sort_type = "asc") {
        $this->DbSqlArr['_orderby'] = " order by '" . $field . "' " . $sort_type;
        return $this->DbSqlArr;
    }

    public function limit($offset = '0', $rows = "") {
        $this->DbSqlArr['_limit'] = " limit " . $offset . "," . $rows;
        return $this->DbSqlArr;
    }

    public function fetchAll() {
        if (!empty($this->DbSqlArr)) {
            $sql = implode("", $this->DbSqlArr);
            $result = $this->query->$sql;
            return $result;
        }
    }

    public function query($sql, $query_mode = "all", $debug = false) {
        if ($debug == true) {
            var_dump($sql);die;
        } else {
            try {
                //使用预处理语句来执行sql
                $query = $this->DbConnect->query($sql);
                if ($query) {
                    $query->setFetchMode(PDO::FETCH_ASSOC);
                    if ($query_mode == "all") {
                        $result = $query->fetchAll();
                    } else if ($query_mode == "row") {
                        $result = $query->fetch();
                    }
                }
            } catch (PDOException $e) {
                var_dump($e->getMessage());
            }
        }
        return $result;
    }

    public function exec($sql, $debug = false) {
        if ($debug == true) {
            var_dump($sql);
            die;
        } else {
            $result = $this->DbConnect->exec($sql);
        }
        return $result;
    }

    public function beginTransaction() {
        $this->DbConnect->beginTransaction();
    }

    public function rollback() {
        $this->DbConnect->rollback();
    }

    public function commit() {
        $this->DbConnect->commit();
    }

    public function destruct() {
        $this->DbConnect = null;
    }
}

?>