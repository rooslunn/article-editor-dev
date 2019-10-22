<?php
error_reporting(E_ALL);

/* Loading configs */
require_once('/home/system/public_html/common/includes/config.php');
require_once('../../crms/includes/config.php');

if (!crms_user::check_current_permissions('ARTICLE_TOOL_VIEW')
	&& !crms_user::check_current_permissions('ARTICLE_TOOL_EDITOR_ROLE')
	&& !crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')
) {
	redirect('/tools/article_editor-dev');
}

include_once(COMMON_INCLUDES_DIR . 'sanitize.php');
include_once(COMMON_INCLUDES_DIR . 'functions.php');
include_once(COMMON_INCLUDES_DIR . 'database.php');

environment::add_class_source(__DIR__ . '/core');
environment::add_class_source(__DIR__ . '/app/controllers');
environment::add_class_source(__DIR__ . '/app/models');

define('ARTICLE_EDITOR_ROOT_PATH', '/tools/article_editor-dev/');

if (environment::is_development()) {
	$server_hostname = str_replace('system', 'crazy', $_SERVER['SERVER_NAME']);
} else {
	$server_hostname = 'crazydomains.com.au';
}

define('CRAZY_HOME', $server_hostname);
define('PREVIEW_LOCATION', 'http://' . $server_hostname . '/help/');
define('UPLOAD_DIR', 'public/temp/');
define('DB_NAME', 'sYra_help');

/* Loading externals */
require_once COMMON_CLASSES_DIR . 'database.php';
require_once COMMON_CLASSES_DIR . 'template.php';

/* Composer */
require __DIR__.'/vendor/autoload.php';

/*
 * Shared DB
 */
app()->instance('db', new Database(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_NAME));

/* Twig */
$twig_loader = new \Twig\Loader\FilesystemLoader('templates/_twig');
app()->instance('twig', new Twig\Environment($twig_loader));

/*
 * Full Steam Ahead!
 */
/* todo: migrate to Kernel */
router::init();
router::start();
