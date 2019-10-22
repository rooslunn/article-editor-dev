<?php

class article_editor_controller {

    const SEARCH_RESULTS = 'Search Results';
    /**
	 * @var template - Sigma template object
	 */
	private $_template;

	/**
	 * @var article_editor Model object
	 */
	private $article_editor,
			$article_content_issues;

	private $moderator_member_id = array(
		'production' => 0,
		'development' => 7165901,
	);

	private $moderator_crms_user_id = array(
		'production' => 0,
		'development' => 3187,
	);

	private $message_preview_length = 15;

	/**
	 * Initializing private classes
	 */
	public function __construct() {
		$this->_template = new template();
		$this->article_editor = new article_editor();
		$this->article_content_issues = new help_section\article_content_issues();
	}

	/**
	 * Main index page
	 * @return void
	 */
	public function index() {
		if (input::has('section_name')) {
			$this->_template->loadTemplateFile('templates/article_list.tpl');

			if (input::get('section_name') != '') {
				$replace = preg_replace('/\-/', ' ', input::get('section_name'));
			} else {
				$replace = self::SEARCH_RESULTS;
			}

			$this->_template->setVariable('current_section_name', $replace);
			$this->parse_sections()->get_articles();
		} else if (input::has('article_id')) {
			if (input::get('section_name') != '') {
				$replace = preg_replace('/\-/', ' ', input::get('section_name'));
			} else {
				$replace = self::SEARCH_RESULTS;
			}

			$this->_template->loadTemplateFile('templates/article_list.tpl');
			$this->_template->setVariable('current_section_name', $replace);
			$this->parse_sections()->get_articles();
		} else {
		    $current_section_name = $this->resolveSectionName(input::get('section_name'));
		    $sections = $this->article_editor->get_sections_list();
		    
		    $totals = $this->article_editor->get_articles_statuses_amount();
            $totals['untagged'] = ($totals['published'] + $totals['unpublished'] + $totals['hold'] + $totals['finished'] - $totals['tagged']);
			$totals['comments_count'] = $this->article_editor->get_article_comments_count();
            
            echo app('twig')->render('index', compact('current_section_name', 'sections', 'totals'));

//			$this->_template->loadTemplateFile('templates/index.tpl');
//			$this->_template->setVariable('current_section_name', $replace);
//			$this->parse_sections()->show_article_editor_header();
//			$this->get_new_articles('new', 5);
//			$this->get_new_articles('last', 5);
		}

		if (crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')) {
			$this->_template->touchBlock('publish_role');
		} else if (crms_user::check_current_permissions('ARTICLE_TOOL_EDITOR_ROLE')) {
			$this->_template->touchBlock('edit_role');
			$this->_template->hideBlock('publish_role');
		} else {
			$this->_template->hideBlock('edit_role');
			$this->_template->hideBlock('edit_role_footer');
		}

		$this->_template->show();
	}

	/**
	 * Show article preview
	 *
	 * @param bool $open_on_crazy if set it false - article will be opened in tool, if true - on crazy
	 * @return void
	 */
	public function preview($open_on_crazy = true) {
		$article_id = input::get('id');

		if ($article_id && $article = $this->article_editor->get_article_data($article_id)) {
			if ($open_on_crazy) {
				header('Location: '. PREVIEW_LOCATION . $article['article_url'] . '/?debug=1');

				exit();
			} else {
				$this->_template->loadTemplateFile('templates/preview.tpl');

				$article['article_content'] = $this->article_editor->preview_image_src_replace($article['article_content'], $article_id);
				$this->_template->setVariable($article);

				$this->_template->show();
			}
		}
	}

	public function add() {
		if (!crms_user::check_current_permissions('ARTICLE_TOOL_EDITOR_ROLE') &&
			!crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')
		) {
			redirect('/tools/article_editor-dev');
		}

		$this->_template->loadTemplateFile('templates/editor.tpl');
		$this->_template->show();
	}

	public function new_articles_list() {
		if (input::has('article_id')) {
			if (input::get('section_name') != '') {
				$replace = preg_replace('/\-/', ' ', input::get('section_name'));
			} else {
				$replace = self::SEARCH_RESULTS;
			}

			$this->_template->loadTemplateFile('templates/article_list.tpl');
			$this->_template->setVariable('current_section_name', $replace);
			$this->parse_sections()->get_articles();
		} else {
			$this->_template->loadTemplateFile('templates/new_articles_list.tpl');
			$this->parse_sections()->get_new_articles('new', 0);
		}

		if (crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')) {
			$this->_template->touchBlock('publish_role');
		} else if (crms_user::check_current_permissions('ARTICLE_TOOL_EDITOR_ROLE')) {
			$this->_template->touchBlock('edit_role');
			$this->_template->hideBlock('publish_role');
		} else {
			$this->_template->hideBlock('edit_role');
			$this->_template->hideBlock('edit_role_footer');
		}

		$this->_template->show();
	}

	public function articles_missing_tags_list() {
		if (input::has('article_id')) {
			if (input::get('section_name') != '') {
				$replace = preg_replace('/\-/', ' ', input::get('section_name'));
			} else {
				$replace = self::SEARCH_RESULTS;
			}

			$this->_template->loadTemplateFile('templates/article_list.tpl');
			$this->_template->setVariable('current_section_name', $replace);
			$this->parse_sections()->get_articles();
		} else {
			$this->_template->loadTemplateFile('templates/articles_missing_tags_list.tpl');
			$this->parse_sections()->get_new_articles('missing', 0);
		}

		if (crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')) {
			$this->_template->touchBlock('publish_role');
		} else if (crms_user::check_current_permissions('ARTICLE_TOOL_EDITOR_ROLE')) {
			$this->_template->touchBlock('edit_role');
			$this->_template->hideBlock('publish_role');
		} else {
			$this->_template->hideBlock('edit_role');
			$this->_template->hideBlock('edit_role_footer');
		}

		$this->_template->show();
	}

	public function article_list_status() {
		if (input::has('article_id')) {
			if (input::get('section_name') != '') {
				$replace = preg_replace('/\-/', ' ', input::get('section_name'));
			} else {
				$replace = self::SEARCH_RESULTS;
			}

			$this->_template->loadTemplateFile('templates/article_list.tpl');
			$this->_template->setVariable('current_section_name', $replace);
			$this->parse_sections()->get_articles();
			$this->_template->show();
		}

		if (input::has('status')) {
			$status_name = strtolower(input::get('status'));
		} else {
			$status_name = 'published';
		}

		switch ($status_name) {
			case 'finished': $status_id = 1; $current_section_name = 'Finished Articles'; break;
			case 'delete': $status_id = 2; $current_section_name = 'Deleted Articles'; break;
			case 'published': $status_id = 3; $current_section_name = 'Published Articles'; break;
			case 'hold': $status_id = 4; $current_section_name = 'Hold Articles'; break;
			case 'unpublished': $status_id = 5; $current_section_name = 'Unpublished Articles'; break;
			default: $status_id = 3; $current_section_name = 'Published Articles';
		}

		$this->_template->loadTemplateFile('templates/article_list_status.tpl');
		$this->_template->setVariable('current_section_name', $current_section_name);
		$this->parse_sections()->get_new_articles('status', 0);

		if (crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')) {
			$this->_template->touchBlock('publish_role');
		} else if (crms_user::check_current_permissions('ARTICLE_TOOL_EDITOR_ROLE')) {
			$this->_template->touchBlock('edit_role');
			$this->_template->hideBlock('publish_role');
		} else {
			$this->_template->hideBlock('edit_role');
			$this->_template->hideBlock('edit_role_footer');
		}

		$this->_template->show();
	}

	public function last_updated_articles_list() {
		if (input::has('article_id')) {
			if (input::get('section_name') != '') {
				$replace = preg_replace('/\-/', ' ', input::get('section_name'));
			} else {
				$replace = self::SEARCH_RESULTS;
			}

			$this->_template->loadTemplateFile('templates/article_list.tpl');
			$this->_template->setVariable('current_section_name', $replace);
			$this->parse_sections()->get_articles();
		} else {
			$this->_template->loadTemplateFile('templates/last_updated_list.tpl');
			$this->parse_sections()->get_new_articles('last', 0);
		}

		if (crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')) {
			$this->_template->touchBlock('publish_role');
		} else if (crms_user::check_current_permissions('ARTICLE_TOOL_EDITOR_ROLE')) {
			$this->_template->touchBlock('edit_role');
			$this->_template->hideBlock('publish_role');
		} else {
			$this->_template->hideBlock('edit_role');
			$this->_template->hideBlock('edit_role_footer');
		}

		$this->_template->show();
	}

	/**
	 * Show edit page
	 */
	public function edit() {
		if (!crms_user::check_current_permissions('ARTICLE_TOOL_EDITOR_ROLE')
			&& !crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')) {
			redirect('/tools/article_editor-dev');
		}

		$this->_template->loadTemplateFile('templates/editor.tpl');

		$article = array();
		if (input::has('article_id')) {
			$article = $this->article_editor->get_article_data(input::get('article_id'));

			if (empty($article)) {
				header('Location: ' . ARTICLE_EDITOR_ROOT_PATH . 'edit'); exit;
			}
		}

		if (!empty($article)) {
			$article_issues = $this->article_content_issues->check_current_article_for_issues(input::get('article_id'));
			if ($article_issues !== true) {
				$this->_template->setCurrentBlock('issue_block');
				$this->_template->setVariable(array('article_issues' => $article_issues));
				$this->_template->parseCurrentBlock();
			}

			/** @TODO remove article_tags field and replace articles from uncategorized article sections */
			$article['article_tags'] = $article['section_title'] ? $article['section_title'] : $article['article_tags'];
			$article['section_name'] = str_replace(' ', '-', $article['article_tags']);
			$article['article_tags'] .= !$article['section_title'] ? '(uncategorized)' : '';

			$article['action'] = 'update';
			$article['article_input_title'] = $article['article_title'];
			$article['article_images'] = $this->article_editor->get_article_attachments(input::get('article_id'));
			$article['date_scanned'] = date('j M Y', strtotime($article['date_scanned']));
			$article['date_published'] = ($article['date_published'] == '0000-00-00 00:00:00') ? '-' : date('j M Y', strtotime($article['date_published']));
			$article['date_updated'] = ($article['date_updated'] == '0000-00-00 00:00:00') ? '-' : date('j M Y', strtotime($article['date_updated']));

			$article['search_tags_count'] = sizeof($article['article_search_tags']);
			$article['article_search_tags'] = implode("\n", $article['article_search_tags']);

			$article['related_sections'] = $this->article_editor->get_article_additional_sections($article['article_id']);

			if (in_array($article['doc_type'], array('guide', 'tutorial'))) {
				$this->_template->touchBlock($article['doc_type'].'_selected');
			}
		} else {
			$article = array(
				'section_name' => 'Category',
				'article_tags' => 'Category',
				'article_id' => 0,
				'article_title' => 'Title',
				'article_input_title' => '',
				'article_url' => '',
				'article_description' => '',
				'article_content' => '',
				'article_images' => array(),
				'related_sections' => array(),
				'action' => 'save',
			);

			$this->_template->hideBlock('guide_selected');
		}

		$this->_template->setCurrentBlock('article_images');

		foreach ($article['article_images'] as $image) {
			$this->_template->setVariable($image);
			$this->_template->parseCurrentBlock();
		}

		$statuses = $this->article_editor->get_statuses_list();
		$this->_template->setCurrentBlock('article_status_row');

		foreach ($statuses as $status) {
			if ($article['status_id'] == $status['status_id']) {
				$status['status_selected'] = 'selected="selected"';
			}

			if ((3 == $status['status_id'] || 5 == $status['status_id']) //3-Published, 5-Unpublished
				&& !crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')
			) {
				continue;
			}

			$this->_template->setVariable($status);
			$this->_template->parseCurrentBlock();
		}

		$valid_locales = array('ae', 'au', 'in', 'nz', 'uk', 'us', 'hk', 'id', 'my', 'ph', 'sg');
		$this->_template->setCurrentBlock('excluded_article_row');

		foreach ($valid_locales as $excluded_locale) {
			$exclude_data = array(
				'excluded_locale' => $excluded_locale,
				'excluded_locale_uc' => strtoupper($excluded_locale),
				'excluded_locale_selected' => in_array($excluded_locale, $article['excluded_locales']) ? 'selected="selected"' : '',
			);

			$this->_template->setVariable($exclude_data);
			$this->_template->parseCurrentBlock();
		}

		$valid_resellers_to_exclude = array(
			1 => 'AustDomains',
			1344 => 'CrazyDomains',
			1023 => 'Sitebeat',
		);

		$this->_template->setCurrentBlock('excluded_reseller_row');

		foreach ($valid_resellers_to_exclude as $reseller_id => $reseller_name) {
			$exclude_reseller_data = array(
				'exclude_reseller_id' => $reseller_id,
				'excluded_reseller_name' => $reseller_name,
				'excluded_reseller_selected' => in_array($reseller_id, $article['excluded_resellers']) ? 'selected="selected"' : '',
			);

			$this->_template->setVariable($exclude_reseller_data);
			$this->_template->parseCurrentBlock();
		}

		$sections = $this->article_editor->get_sections_list();

		if (!input::has('article_id')) {
			array_unshift(
				$sections,
				array(
					'section_selected' => 'selected="selected"',
					'section_id' => '',
					'section_title' => '-- Select Category --',
					'option_disabled' => 'disabled="disabled"',
				)
			);
		}

		foreach ($sections as $section) {
			if ($article['article_tags'] == $section['section_title']) {
				$section['option_section_selected'] = 'selected="selected"';
			}

			$section['option_section_title'] = $section['section_title'];
			$section['option_section_id'] = $section['section_id'];
			unset($section['section_title'], $section['section_id']);

			$this->_template->setCurrentBlock('article_sections_row');
			$this->_template->setVariable($section);
			$this->_template->parseCurrentBlock();

			$this->_template->setCurrentBlock('related_section_row');

			if ($section['option_section_id'] == $article['section_id']) {
				$section['option_disabled'] = 'disabled';
				$section['option_section_selected'] = 'selected="selected"';
			}

			if (in_array($section['option_section_id'], $article['related_sections'])) {
				$section['option_section_selected'] = 'selected="selected"';
			}

			$this->_template->setVariable($section);
			$this->_template->parseCurrentBlock();
		}

		unset($article['status_name'], $article['status_id']);

		if (crms_user::check_current_permissions('ARTICLE_TOOL_PUBLISHER_ROLE')) {
			$this->_template->touchBlock('publish_role');
		}else{
			$this->_template->hideBlock('publish_role');
		}

		$this->_template->setVariable($article);
		$this->_template->show();
	}

	public function save_article() {
		echo json_encode($this->article_editor->save_article(input::get_array('post')));
	}

	public function update_article() {
		echo json_encode($this->article_editor->update_article(input::get_array('post')));
	}

	public function upload_files() {
		echo json_encode($this->article_editor->upload_images(input::get_files()));
	}

	public function check_doc_type() {
		echo json_encode($this->article_editor->check_doc_type(input::get_array('post')));
	}

	public function remove_files() {
		$article_id = input::get('article_id');
		$image_id = input::get('image_id');
		$error = true;

		if ($image_id) {
			if ($this->article_editor->delete_image($image_id, $article_id)) {
				$error = false;
			}
		}

		if ($error) {
			$data = array('error' => 'There was an error deleting your files!');
		} else {
			$data = array('success' => 'Images was successfully deleted!');
		}

		echo json_encode($data);
	}

	public function update_status() {
		if (crms_user::check_current_permissions('ARTICLE_TOOL_VIEW')) {
			redirect('/tools/article_editor-dev');
		}

		if (!input::has('values') || !input::has('status')) {
			echo json_encode(0);

			return;
		}

		echo json_encode(($this->article_editor->update_status(input::get('values'), input::get('status')) ? 1 : 0));
		return;
	}

	public function get_image() {
		$image_id = input::get('id');
		if ($image_id && $image = $this->article_editor->get_image($image_id)) {
			header("Content-type: " . $image['mime_type']);
			echo $image['image'];
			exit;
		}

		header("Location: /");
	}

	public function article_comments() {
		$this->_template->loadTemplateFile('templates/article_comments.tpl');
		$template_data = array();

		// Login token
		$moderator_member_id = environment::is_development() ? $this->moderator_member_id['development'] : $this->moderator_member_id['production'];
		$moderator_crms_user_id = environment::is_development() ? $this->moderator_crms_user_id['development'] : $this->moderator_crms_user_id['production'];

		$moderator_details = $this->article_editor->get_comment_moderator_login_details($moderator_crms_user_id, $moderator_member_id);

		$login_as_token = array(
			'member_id' => $moderator_details['member_id'],
			'reseller_id' => $moderator_details['reseller_id'],
			'crms' => array(
				'username' => $moderator_details['username'],
				'password' => $moderator_details['password'],
			),
			'expiry' => time() + 1800,
		);

		$cryptography = new \cryptography();
		$cryptography->set_iv($cryptography->generate_iv());
		$cryptography->set_key(LOGIN_IN_AS_ENCRYPTION_KEY);

		$login_as_member_token = urlencode($cryptography->encrypt(serialize($login_as_token), true));

		$template_data['article_comments_title'] = 'Article Comments';
		$filter = !empty($_GET['status']) ? array('status' => $_GET['status']) : array();
		$comments = $this->article_editor->get_comments($filter);
		$this->_template->addBlockfile('article_comments', 'article_comments', 'templates/includes/article_comments.tpl');

		$this->_template->touchBlock('article_comments');

		$this->_template->setCurrentBlock('comment_record');


		foreach ($comments as $comment_id => $comment) {
			$comment_data = array(
				'discuss_comment_id' => $comment_id,
				'discuss_article_id' => $comment['article_id'],
				'discuss_article_title' => $comment['article_title'],
				'discuss_comment_message' => $comment['message'],
				'discuss_posted_by' => $comment['display_name'],
				'discuss_comment_status' => $comment['status_name'],
				'discuss_status_color' => $comment['status_color'],
				'discuss_comment_date_published' => !empty($comment['date_published'])
					? date('j M y G:i', strtotime($comment['date_published'])) : '-',
			);

			if (mb_strlen($comment_data['discuss_comment_message'], 'UTF-8') > $this->message_preview_length) {
				$comment_data['discuss_comment_message_short'] = mb_substr(strip_tags($comment_data['discuss_comment_message']), 0, $this->message_preview_length, 'UTF-8');
				$comment_data['discuss_full_message_click_control'] = '...';
			} else {
				$comment_data['discuss_comment_message_short'] = $comment_data['discuss_comment_message'];
			}

			//it's important to use slash before query part in url (f.e. /?comment_id=60)
			$article_url = urlencode("https://manage." . CRAZY_HOME . "/members/help/{$comment['article_url']}/?comment_id={$comment_id}");
			$comment_data['discuss_article_url'] = "https://manage." . CRAZY_HOME. "/members/member/member_login/?login_as={$login_as_member_token}&redirect={$article_url}";
			$comment_data['discuss_comment_date_added'] = date('j M y G:i', strtotime($comment['date_added']));

			$this->_template->setVariable($comment_data);
			$this->_template->parseCurrentBlock();
		}

		$this->_template->setVariable($template_data);
		$this->_template->show();
	}

	private function parse_sections() {
		$this->_template->setCurrentBlock('article_row_nav');

		$sections_details = $this->article_editor->get_sections_list();

		foreach ($sections_details as $section) {
			$this->_template->setVariable(array(
				'section_title' => $section['section_title'],
				'section_description' => $section['section_description'],
				'section_name' => str_replace(' ', '-', $section['section_title']),
				'total' => $section['total'],
			));

			$this->_template->parseCurrentBlock();

		}

		return $this;
	}

	private function show_article_editor_header() {
		$this->_template->setCurrentBlock('article_editor_header');

		$calculate_statuses = $this->article_editor->get_articles_statuses_amount();

		$this->_template->setVariable(array(
			'published' => $calculate_statuses['published'],
			'unpublished' => $calculate_statuses['unpublished'],
			'finished' => $calculate_statuses['finished'],
			'hold' => $calculate_statuses['hold'],
			'helpful' => $calculate_statuses['helpful'],
			'unhelpful' => $calculate_statuses['unhelpful'],
			'untagged' => $calculate_statuses['untagged'] = ($calculate_statuses['published'] + $calculate_statuses['unpublished'] + $calculate_statuses['hold'] + $calculate_statuses['finished'] - $calculate_statuses['tagged']),
			'comments_count' => $this->article_editor->get_article_comments_count(),
		));

		$this->_template->parseCurrentBlock();

		return $this;
	}

	private function get_articles() {
		$articles = $this->article_editor->get_articles();

		$this->_template->setCurrentBlock('article_row');

		foreach ($articles as $article) {
			$article['date_scanned'] = date('j M y G:i', strtotime($article['date_scanned']));
			if ($article['date_updated'] != '0000-00-00 00:00:00') {
				$article['date_scanned'] = date('j M y G:i', strtotime($article['date_updated']));
			}

			$article['article_url'] = PREVIEW_LOCATION . $article['article_url'];
			$article['css_status_id'] = strtolower($article['status']);

			$this->_template->setVariable($article);
			$this->_template->parseCurrentBlock();
		}
	}

	private function get_new_articles($sort_of_article = 'new', $limit = 0) {
		if (!isset($limit) || ($limit == 0) || (intval($limit == 0))) {
			$limit = 0;
		}

		if ($sort_of_article == 'new') {
			$articles = $this->article_editor->get_new_articles('new', $limit);
			$this->_template->setCurrentBlock('new_articles_row');
		}

		if ($sort_of_article == 'last') {
			$articles = $this->article_editor->get_new_articles('last', $limit);
			$this->_template->setCurrentBlock('last_updated_articles_row');
		}

		if ($sort_of_article == 'missing') {
			$articles = $this->article_editor->get_articles_with_missing_tags();
			$this->_template->setCurrentBlock('missing_tags_articles');
		}

		if ($sort_of_article == 'status') {

			$status_name = strtolower(input::get('status'));
			$article_statuses = $this->article_editor->get_article_statuses();
			$status_name  = !empty($article_statuses[$status_name]) ? $status_name : 'published';

			$articles = $this->article_editor->get_articles_by_status($status_name);
			$this->_template->setCurrentBlock('articles_by_status_row');
		}

		foreach ($articles as $article) {
			$article['date_scanned'] = date('j M y G:i', strtotime($article['date_scanned']));

			if ($sort_of_article == 'last') {
				$article['date_scanned'] = date('j M y G:i', strtotime($article['date_updated']));
			}

			if ($sort_of_article == 'status') {
				$article['date_scanned'] = date('j M y G:i', strtotime($article['date_updated']));
			}

			$article['article_url'] = PREVIEW_LOCATION . $article['article_url'];
			$article['css_status_id'] = strtolower($article['status']);

			$this->_template->setVariable($article);
			$this->_template->parseCurrentBlock();
		}
	}

    /**
     * @param $section_name
     * @return string|string[]|null
     */
    private function resolveSectionName($section_name)
    {
        $replace = self::SEARCH_RESULTS;
        if (empty($section_name)) {
            $replace = preg_replace('/-/', ' ', $section_name);
        }
        return $replace;
    }


}
