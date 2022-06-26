<?php
require_once 'config.php';

class Db
{
    private static $instance;
    private $connection;

    private function __construct() {}

    public function __clone() {
        throw new Exception('Нельзя, запрещено!');
    }

    private static function getInstance() {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
        }

        return self::$instance;
    }

    private static function initConnection() {
        $db = self::getInstance();
        $db->connection = new mysqli(db_host, db_user, db_pass, db_name);
        $db->connection->set_charset('utf8');
        return $db;
    }

    public static function getConnection() {
        try {
            $db = self::initConnection();
            return $db->connection;
        } catch (Exception $ex) {
            echo "Ошибка подключения к БД: " . $ex->getMessage();
            return null;
        }
    }

    public static function escape ($str) {
        $dbconn = self::getConnection();
        return $dbconn->real_escape_string($str);
    }
}
?>