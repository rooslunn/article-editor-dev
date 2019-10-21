<?php

class input {
	private static $_instance = null;
	private static $_arrays = array(
		'get' => array(),
		'post' => array(),
		'all' => array(),
		'files' => array(),
	);

	private function __construct() {
	}

	public static function init() {
		if (self::$_instance === null) {
			self::$_instance = new self();
		}

		self::_collect_arrays();

		return self::$_instance;
	}

	private static function _collect_arrays() {
		self::$_arrays = array(
			'get' => self::filter_variables($_GET),
			'post' => self::filter_variables($_POST),
			'files' => $_FILES,
		);
		self::$_arrays['all'] = array_merge(self::$_arrays['get'], self::$_arrays['post']);
	}

	public static function filter_variables($variables) {
		$variables = self::_to_number($variables);

		return $variables;
	}

	private static function _to_number($variable) {
		if (is_array($variable)) {
			foreach ($variable as $key => $value) {
				$variable[$key] = self::_to_number($value);
			}

			return $variable;
		} else {
			return is_numeric($variable) ? 0 + $variable : $variable;
		}
	}

	public static function get($key, $from = '') {
		if (!empty($from) && !in_array($from, array_keys(self::$_arrays))) {
			return null;
		}

		return empty($from) ? self::$_arrays['all'][$key] : self::$_arrays[$from][$key];
	}

	public static function all() {
		return self::$_arrays['all'];
	}

	public static function get_array($type = 'get') {
		if (!in_array($type, array_keys(self::$_arrays))) {
			return null;
		}
		return self::$_arrays[$type];
	}

	public static function get_files() {
		return self::$_arrays['files'];
	}

	public static function has($key) {
	    return array_key_exists($key, self::$_arrays['all']);
//		return is_null(self::$_arrays['all'][$key]) ? false : true;
	}
}