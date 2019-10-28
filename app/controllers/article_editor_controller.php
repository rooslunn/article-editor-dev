<?php

use Dreamscape\Foundation\ACL;
use Dreamscape\Model\Article;
use Dreamscape\Repository\ArticleCirlcesRepository;
use Dreamscape\Repository\ArticleRepository;
use Dreamscape\Repository\ArticleStatusRepository;
use Dreamscape\Repository\Enum\DocTypeEnum;
use Dreamscape\Repository\Enum\LocaleEnum;
use Dreamscape\Repository\Enum\ResellerEnum;
use Dreamscape\Repository\Repository;
use Dreamscape\Repository\SectionRepository;
use Dreamscape\Validators\ArticleValidator;

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

    public function index()
    {
        ACL::authUser();

        $circlesRepository = new ArticleCirlcesRepository();
        $circles = $circlesRepository->all();

        $articles = new ArticleRepository();
        $recently_inserted = $articles->recenltyInserted(Repository::RECENTLY_QUERY_LIMIT);
        $recently_updated = $articles->recenltyUpdated(Repository::RECENTLY_QUERY_LIMIT);

        $sections = (new SectionRepository())->getAll();

        display('index',
            compact('sections', 'circles', 'recently_inserted', 'recently_updated')
        );
    }

    public function article_list()
    {
        /* todo: make Track Timings global (?Iginition, ?Blackfire) */
        $timings = [];
        $timings['01-start'] = ($end = microtime(true));

        $filters = input::expose(ArticleRepository::FILTERS);
        $articles = (new ArticleRepository())->filterBy($filters);
        $timings['02-articles'] = microtime(true) - $end;
        $end = microtime(true);

        $section_title = input::get('section_name');
        /* todo: Share data between views (cache?, ajax?) */
        $sections = (new SectionRepository())->getAll();
        $timings['03-sections'] = microtime(true) - $end;
        $end = microtime(true);

        $timings_json = json_encode($timings);

        $view = view('article_list', compact('sections', 'section_title', 'articles', 'timings_json'));
        $timings['06-render'] = microtime(true) - $end;
        echo $view;

//        display('article_list', compact('sections', 'section_title', 'articles', 'timings_json'));
    }

    public function organizer()
    {
        display('organizer');
    }

    public function edit()
    {
        ACL::authUser();

        $article_id = Article::filterId(input::get('article_id'));

        $sections = (new SectionRepository())->getAll();
        $statuses = (new ArticleStatusRepository())->getAll();

        $doc_types = DocTypeEnum::all();
        $locales = LocaleEnum::all();
        $resellers = ResellerEnum::all();

        $article_check = ArticleValidator::check($article_id);
        $article = (new ArticleRepository())->findOrNew($article_id);

        display('editor',
            compact('article', 'article_check', 'doc_types', 'sections',
                'statuses', 'locales', 'resellers')
        );
    }

    public function preview() {
        if (! $article_id = Article::filterId(input::get('article_id'))) {
            throw new \InvalidArgumentException('Article id not provided');
        }

        $article = (new ArticleRepository())->articleId($article_id);
        redirect(PREVIEW_LOCATION . $article['article_url'] . '/?debug=1');
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
