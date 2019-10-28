<?php

class router {
	private static $_instance = null;
	private static $_root_path = ARTICLE_EDITOR_ROOT_PATH;

	private function __construct(){}
	
	public static function init() {
		if (self::$_instance === null) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
	
	public static function start() {
		input::init();

		$routes = self::_get_routes();
		$request_uri = preg_replace('/\?[\s\S]+/', '', $_SERVER['REQUEST_URI']);

		if (array_key_exists(rtrim($request_uri, '/'), $routes)) {
			list($class, $method) = explode('@', $routes[rtrim($request_uri, '/')]);
			$controller = new $class();
			$controller->$method();
		} else {
			self::_error_404();
		}
	}

	private static function _error_404() {
		echo '<h1>404 Not found.</h1>';
	}

	private static function _get_routes() {
		/* List of routes here */
		return array(
			rtrim(self::$_root_path, '/') => 'article_editor_controller@index',
            self::$_root_path . 'article_list' => 'article_editor_controller@article_list',
            self::$_root_path . 'organizer' => 'article_editor_controller@organizer',
			self::$_root_path . 'edit' => 'article_editor_controller@edit',
            self::$_root_path . 'preview' => 'article_editor_controller@preview',

		    /* todo: Routes	 */
			self::$_root_path . 'new_articles_list' => 'article_editor_controller@new_articles_list',
			self::$_root_path . 'article_list_status' => 'article_editor_controller@article_list_status',
			self::$_root_path . 'articles_missing_tags_list' => 'article_editor_controller@articles_missing_tags_list',
			self::$_root_path . 'last_updated_articles_list' => 'article_editor_controller@last_updated_articles_list',

			self::$_root_path . 'get_image' => 'article_editor_controller@get_image',
			self::$_root_path . 'article_comments' => 'article_editor_controller@article_comments',

			self::$_root_path . 'ajax/update_status' => 'article_editor_controller@update_status',
			self::$_root_path . 'ajax/update_article' => 'article_editor_controller@update_article',
			self::$_root_path . 'ajax/save_article' => 'article_editor_controller@save_article',
			self::$_root_path . 'ajax/upload_files' => 'article_editor_controller@upload_files',
			self::$_root_path . 'ajax/delete_files' => 'article_editor_controller@remove_files',
			self::$_root_path . 'ajax/check_doc_type' => 'article_editor_controller@check_doc_type',
		);
	}
}
