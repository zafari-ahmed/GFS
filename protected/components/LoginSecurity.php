<?php

/**
 * Login input hardening against SQL injection / destructive queries.
 */
class LoginSecurity
{
	const EMAIL_MAX_LENGTH = 100;
	const PASSWORD_MAX_LENGTH = 128;

	public static function inspect($email, $password)
	{
		$email = self::cleanScalar($email);
		$password = self::cleanScalar($password);

		if ($email === '' || $password === '') {
			return self::fail('Please enter email/username and password.');
		}
		if (strlen($email) > self::EMAIL_MAX_LENGTH || strlen($password) > self::PASSWORD_MAX_LENGTH) {
			return self::fail('Invalid login details.');
		}
		if (self::containsNullByte($email) || self::containsNullByte($password)) {
			return self::fail('Invalid login details.');
		}
		if (self::looksLikeSql($email) || self::hasDestructiveSql($email) || self::hasDestructiveSql($password)) {
			return self::fail('Invalid login details.');
		}
		if (!self::isSafeLoginIdentity($email)) {
			return self::fail('Invalid login details.');
		}

		return array(
			'ok' => true,
			'email' => $email,
			'password' => $password,
			'message' => '',
			'blocked' => false,
		);
	}

	protected static function fail($message)
	{
		return array(
			'ok' => false,
			'email' => '',
			'password' => '',
			'message' => $message,
			'blocked' => true,
		);
	}

	public static function cleanScalar($value)
	{
		if (is_array($value) || is_object($value)) {
			return '';
		}
		$value = (string)$value;
		$value = str_replace(array("\0", "\r", "\n", "\t"), '', $value);
		return trim($value);
	}

	public static function containsNullByte($value)
	{
		return strpos($value, "\0") !== false;
	}

	/**
	 * Email or username only: letters, numbers, and . _ @ + -
	 */
	public static function isSafeLoginIdentity($value)
	{
		return (bool)preg_match('/^[A-Za-z0-9._@+-]{3,'.self::EMAIL_MAX_LENGTH.'}$/', $value);
	}

	public static function looksLikeSql($value)
	{
		$patterns = array(
			'/\b(select|delete|drop|truncate|update|insert|union|alter|exec|execute|merge|replace|grant|revoke|create|rename)\b/i',
			'/\b(from|into|where|table|database|schema|information_schema)\b/i',
			'/(--|#|\/\*|\*\/|;)/',
			'/\b(or|and)\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+/i',
			'/\b(or|and)\s+[\'"].+[\'"]\s*=\s*[\'"]/i',
			'/\b(sleep|benchmark|load_file|outfile|dumpfile|concat)\s*\(/i',
			'/\binto\s+(out|dump)file\b/i',
			'/\bxp_\w+/i',
			'/\b0x[0-9a-f]{8,}\b/i',
			'/[\'"]\s*(or|and)\s+[\'"]/i',
			'/\bunion\s+select\b/i',
		);
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $value)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Catch stacked / inner queries in any login field, including password.
	 * Example: '; DELETE FROM users WHERE id <> 1
	 */
	public static function hasDestructiveSql($value)
	{
		$patterns = array(
			'/;\s*(select|delete|drop|truncate|update|insert|alter|create|grant|revoke|union)\b/i',
			'/\b(delete|drop|truncate)\s+(from|table|database)\b/i',
			'/\bdrop\s+(table|database|schema)\b/i',
			'/\bupdate\s+\w+\s+set\b/i',
			'/\binsert\s+into\b/i',
			'/\bwhere\s+id\s*(<>|!=|=)/i',
		);
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $value)) {
				return true;
			}
		}
		return false;
	}
}
