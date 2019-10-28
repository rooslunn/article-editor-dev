<?php

use Dreamscape\Foundation\ACL;

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
//require_once COMMON_CLASSES_DIR . 'database.php';
require_once COMMON_CLASSES_DIR . 'template.php';

/* Composer */
require __DIR__.'/vendor/autoload.php';

/*
 * Shared DB
 */
app()->bind('db', new \Dreamscape\Database\DatabaseContracted(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_NAME));

/* Twig */
$twig_settings = [];
if (! environment::is_development()) {
    $twig_settings['cache'] = '/templates/_twig/_cache';
}
$twig_loader = new \Twig\Loader\FilesystemLoader('templates/_twig');
$twig = new Twig\Environment($twig_loader, $twig_settings);

$twig_ext_funcs = [
    'attribute_if_in' => static function ($needle, $haystack, $attribute) {
        if (! is_array($haystack)) {
            $haystack = [$haystack];
        }
        return in_array($needle, $haystack, false) ? $attribute : '';
    },
];

$twig_ext_filters = [
    'only_allowed' => static function ($value, array $allowed) {
        return in_array($value, $allowed, false);
    },
];

foreach ($twig_ext_funcs as $name => $closure) {
    $twig->addFunction(new \Twig\TwigFunction($name, $closure));
}
foreach ($twig_ext_filters as $name => $closure) {
    $twig->addFilter(new \Twig\TwigFilter($name, $closure));
}

$twig->addGlobal('permissions', ACL::roles());
$twig->addGlobal('TWIG_PREVIEW_LOCATION', PREVIEW_LOCATION);
$twig->addGlobal('TWIG_REQUEST_URI', $_SERVER['REQUEST_URI']);

app()->bind('twig', $twig);

/*
 * Full Steam Ahead!
 */
/* todo: simplify to Kernel */
/* todo: migrate to Router (Symfony?) */
router::init();
router::start();
