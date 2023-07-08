<?php

namespace Core;

use PDO;
use App\Config;

/**
 * Base model
 *
 * PHP version 5.4
 */
abstract class Model
{
    /**
     * hold the database connection
     * @var object
     */
    protected $_db;

    /**
     * create a new instance of the database helper
     */
    public function __construct()
    {
        //connect to PDO here.
        $this->_db = Database::getDB();
    }
    
    /**
     * Get the PDO database connection
     *
     * @return \PDO
     */
    protected static function getDB()
    {
        static $db = null;

        if ($db === null) {
            $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8';
            $db = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);

            // Throw an Exception when an error occurs
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $db;
    }
}
