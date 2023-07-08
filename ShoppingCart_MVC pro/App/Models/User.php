<?php

namespace App\Models;

use Exception;
use PDO;

/**
 * Post model
 *
 * PHP version 5.4
 */
class User extends \Core\Model
{
    public function createUser(array $data)
    {
        $cols = "";
        $queMarks = "";
        $keys = array_keys($data);
        $values = array_values($data);
        
        //php中用count,java用arr.length
        for ($i = 0; $i < count($keys); $i++) {
            $cols .= "`" . "$keys[$i]" . "`, ";
            $queMarks = $queMarks . "?, ";
        }
        $cols = substr($cols, 0, -2);
        $queMarks = substr($queMarks, 0, -2);

        try {
            // $db = static::getdb();
            $db = $this->_db;
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $sql = "INSERT INTO `users` (" . $cols . ") VALUES (" . $queMarks . ")"; 
            $stmt = $db->prepare($sql);
            $flag = $stmt->execute($values);
            if (!$flag) {
                throw new Exception('create error', 999);
            }

            return $this->getUserById($db->lastInsertId());; //lastinsertid 取最後創建的id
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get all the posts as an associative array
     *
     * @return array
     */
    public function verifyUser(string $ac, string $pw)
    {
        try {
            // $db = static::getDB();
            $db = $this->_db;//讓很多表格有相同連線(connection)

            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    //設定 Error Mode
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $sql = "SELECT * 
            FROM users 
            WHERE account = :account AND pw = :pw";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':account', $ac, PDO::PARAM_STR);
            $stmt->bindValue(':pw', $pw, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $db = null;

            return $result;
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getUserById(int $Id)
    {
        try {
            // $db = static::getDB(); //連接db
            $db = $this->_db;
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    //設定 Error Mode
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $sql = "SELECT * FROM `users` WHERE user_id = :user_id FOR UPDATE";
            $stmt = $db->prepare($sql); //將sql進行編譯，此寫法是防sql injection
            $stmt->bindValue(':user_id', $Id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getUserByAccount(string $account)
    {
        try {
            // $db = static::getdb();
            $db = $this->_db;
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $sql = "SELECT * FROM users WHERE account = :account";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':account', $account, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchall(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
