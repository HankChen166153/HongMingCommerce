<?php

namespace Core;

use PDO;
use PDOException;

class Database extends PDO
{
	/**
	 * @var array Array of saved databases for reusing
	 */
	protected static $instances = array();


	/**
	 * Get the PDO database connection
	 *
	 * @return \PDO
	 */
	public static function getDB($group = false)
	{
		// Determining if exists or it's not empty, then use default group defined in config
		$group = !$group ? array(
			'type' => "mysql",
			'host' => $_ENV['DB_HOST'],
			'name' => $_ENV['DB_NAME'],
			'user' => $_ENV['DB_USER'],
			'pass' => $_ENV['DB_PASSWORD']
		) : $group;

		// Group information
		$type = $group['type'];
		$host = $group['host'];
		$name = $group['name'];
		$user = $group['user'];
		$pass = $group['pass'];

		// ID for database based on the group information
		$id = "$type.$host.$name.$user.$pass";

		// Checking if the same
		if (isset(self::$instances[$id])) {
			return self::$instances[$id];
		}

		try {
			// I've run into problem where
			// SET NAMES "UTF8" not working on some hostings.
			// Specifiying charset in DSN fixes the charset problem perfectly!
			$instance = new Database("$type:host=$host;dbname=$name;charset=utf8", $user, $pass);
			$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // 設定 Error Mode
			$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			// Setting Database into $instances to avoid duplication
			self::$instances[$id] = $instance;

			return $instance;
		} catch (PDOException $e) {
			return ['error' => $e->getMessage()];
			//in the event of an error record the error to errorlog.html
			// Logger::newMessage($e);
			// Logger::customErrorMsg();
		}

		// static $db = null;

		// if ($db === null) {
		//     $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8';
		//     $db = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);

		//     // Throw an Exception when an error occurs
		//     $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// }

		// return $db;
	}
}
