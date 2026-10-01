<?php

/**
 * Failed login tracking and IP ban.
 *
 * Change the static variables below (or Yii params) to tune lockout.
 */
class LoginAttempts extends CActiveRecord
{
	/** Failed passwords allowed before the IP is banned */
	public static $maxAttempts = 3;

	/** How long the IP stays banned (minutes). Use 30 or 60. */
	public static $banMinutes = 30;

	/** Only count failures inside this window (minutes) */
	public static $attemptWindowMinutes = 30;

	public function tableName()
	{
		return 'login_attempts';
	}

	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function getMaxAttempts()
	{
		if (isset(Yii::app()->params['loginMaxAttempts']) && Yii::app()->params['loginMaxAttempts'] !== '') {
			return (int)Yii::app()->params['loginMaxAttempts'];
		}
		return (int)self::$maxAttempts;
	}

	public static function getBanMinutes()
	{
		if (isset(Yii::app()->params['loginBanMinutes']) && Yii::app()->params['loginBanMinutes'] !== '') {
			return (int)Yii::app()->params['loginBanMinutes'];
		}
		return (int)self::$banMinutes;
	}

	public static function getAttemptWindowMinutes()
	{
		if (isset(Yii::app()->params['loginAttemptWindowMinutes']) && Yii::app()->params['loginAttemptWindowMinutes'] !== '') {
			return (int)Yii::app()->params['loginAttemptWindowMinutes'];
		}
		return (int)self::$attemptWindowMinutes;
	}

	public static function clientIp()
	{
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			return substr(trim($parts[0]), 0, 45);
		}
		$ip = Yii::app()->request->userHostAddress;
		return $ip ? substr($ip, 0, 45) : '0.0.0.0';
	}

	public static function ensureTables()
	{
		$db = Yii::app()->db;
		$schema = $db->schema;
		if ($schema->getTable('login_attempts', true) === null) {
			$db->createCommand("
				CREATE TABLE `login_attempts` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`ip_address` VARCHAR(45) NOT NULL,
					`email` VARCHAR(255) NULL,
					`created_on` DATETIME NOT NULL,
					PRIMARY KEY (`id`),
					KEY `ip_created` (`ip_address`, `created_on`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8
			")->execute();
			$schema->refresh();
		}
		if ($schema->getTable('ip_bans', true) === null) {
			$db->createCommand("
				CREATE TABLE `ip_bans` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`ip_address` VARCHAR(45) NOT NULL,
					`banned_until` DATETIME NOT NULL,
					`created_on` DATETIME NOT NULL,
					PRIMARY KEY (`id`),
					KEY `ip_until` (`ip_address`, `banned_until`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8
			")->execute();
			$schema->refresh();
		}
	}

	public static function activeBan($ip)
	{
		self::ensureTables();
		return Yii::app()->db->createCommand()
			->select('id, banned_until')
			->from('ip_bans')
			->where('ip_address = :ip AND banned_until > :now', array(
				':ip' => $ip,
				':now' => date('Y-m-d H:i:s'),
			))
			->order('id DESC')
			->queryRow();
	}

	public static function remainingBanMinutes($bannedUntil)
	{
		$left = strtotime($bannedUntil) - time();
		if ($left <= 0) {
			return 0;
		}
		return (int)ceil($left / 60);
	}

	public static function recordFailure($ip, $email)
	{
		self::ensureTables();
		$now = date('Y-m-d H:i:s');
		Yii::app()->db->createCommand()->insert('login_attempts', array(
			'ip_address' => $ip,
			'email' => substr((string)$email, 0, 255),
			'created_on' => $now,
		));

		$window = self::getAttemptWindowMinutes();
		$since = date('Y-m-d H:i:s', time() - ($window * 60));
		$count = (int)Yii::app()->db->createCommand()
			->select('COUNT(*)')
			->from('login_attempts')
			->where('ip_address = :ip AND created_on >= :since', array(
				':ip' => $ip,
				':since' => $since,
			))
			->queryScalar();

		$max = self::getMaxAttempts();
		$banned = false;
		if ($count >= $max) {
			$minutes = self::getBanMinutes();
			Yii::app()->db->createCommand()->insert('ip_bans', array(
				'ip_address' => $ip,
				'banned_until' => date('Y-m-d H:i:s', time() + ($minutes * 60)),
				'created_on' => $now,
			));
			$banned = true;
		}

		return array(
			'count' => $count,
			'remaining' => max(0, $max - $count),
			'banned' => $banned,
			'ban_minutes' => self::getBanMinutes(),
		);
	}

	public static function clearFailures($ip)
	{
		self::ensureTables();
		Yii::app()->db->createCommand()->delete(
			'login_attempts',
			'ip_address = :ip',
			array(':ip' => $ip)
		);
	}
}
