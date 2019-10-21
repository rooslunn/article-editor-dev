<?php
error_reporting(E_ALL);

/* Loading configs */
require_once('/home/system/public_html/common/includes/config.php');
include_once(COMMON_INCLUDES_DIR . 'sanitize.php');
include_once(COMMON_INCLUDES_DIR . 'database.php');

define('DB_NAME', 'sYra_help');

$article_content_issues = new help_section\article_content_issues();

$query = '	SELECT article_id
			FROM article
			WHERE status_id != 2
			ORDER BY article_id ASC';
$result_data = \db::connect('syssql')
	->select($query)
	->execute()
	->fetch_all();

$issues = '';
$issues_total = 0;

foreach ($result_data as $article) {
	$check_results = $article_content_issues->check_current_article_for_issues($article['article_id']);

	if ($check_results !== true) {
		$issues_total++;
		$issues .=	'<hr /><h1>article_id = ' . $article['article_id'] . '</h1><hr />' . $article_content_issues->get_last_error();
	}
}

if (empty($issues)) {
	$issues = 'All seams to be fine:-)';
}
echo 'Total issues: ' . $issues_total . '<hr />';
echo $issues;

exit();
