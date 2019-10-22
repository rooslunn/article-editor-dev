<?php

/*
 * todo: globalScope: status_id <> DELETED
 */

class article_editor {

	/**
	 * @var error variable
	 */
	private $error;

	/**
	 * @var Database PDO db object
	 */
	private	$sYra_help;
	/**
	 * @var array locales on which crazydomains is available
	 */
	private $valid_locales = array('ae', 'au', 'in', 'nz', 'uk', 'us', 'hk', 'id', 'my', 'ph', 'sg');

	private $article_statuses = array();

	//classes
	private $article_keywords_parser,
			$article_weighter,
			$article_content_issues,
			$article_prepare_search;

	public function __construct() {
		$this->sYra_help = new Database(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_NAME);

		//adding class for parsing and saving keywords from articles

		$this->article_keywords_parser = new help_section\article_keywords_parser();

		//adding class for weighting articles

		$this->article_weighter = new help_section\article_weighter($this->sYra_help);

		//adding class for checking issues in articles

		$this->article_content_issues = new help_section\article_content_issues();

		//adding class for preparing the search string for article

		$this->article_prepare_search = new help_section\article_prepare_search();

		//get list with all available article statuses
		$this->article_statuses = $this->get_article_statuses();
	}

	/**
	 * Get article statuses
	 *
	 * @return array like array('Finished' => 1, 'Delete' => 2, ...)
	 */
	public function get_article_statuses() {
		if (!empty($this->article_statuses)) {
			return $this->article_statuses;
		}

		$query = "	SELECT LCASE(status_name), status_id
					FROM sYra_help.generic_status;";
		$statuses = $this->sYra_help->query($query)->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
		return array_map('reset', $statuses);
	}

	/**
	 * @param int $article_id
	 * @param string $article_content
	 */
	private function parse_article_content($article_id, $article_content) {
		if (!isset($article_id, $article_content) || empty($article_content) || (intval($article_id) == 0)) {
			return false;
		}

		//parse keywords and save them to database

		$keyword_data = $this->article_keywords_parser->scan_keywords($article_content)->filter_blacklist_keywords()->get_keyword_data();
		$this->article_keywords_parser->save_article_keywords($article_id, $keyword_data);

		//re-weight article
		$this->article_weighter->set_article_weight(array($article_id));
	}

	/**
	 * @param int $article_id
	 * @param array $excluded_locales
	 */
	private function save_article_excluded_locales ($article_id, $excluded_locales) {
		$this->sYra_help->delete('sYra_help.article_locale', "article_id = :article_id", array(':article_id' => $article_id));

		if (empty($excluded_locales) || !($article_data = $this->get_article_data($article_id))) {
			return;
		}

		$insert_data = array();

		foreach ($excluded_locales as $locale) {
			if (!in_array($locale, $this->valid_locales)) {
				continue;
			}

			$insert_data[] = array(
				'article_id' => $article_id,
				'exclude_locale' => $locale,
				'date_added' => 'NOW()',
			);
		}

		if (!empty($insert_data)) {
			$this->sYra_help->insert($insert_data, 'article_locale');
		}
	}

	/**
	 * Save related sections of article
	 *
	 * @param int $article_id
	 * @param array $related_sections
	 */
	private function save_article_related_sections($article_id, $related_sections) {
		$this->sYra_help->delete('sYra_help.article_to_section', "article_id = :article_id", array(':article_id' => $article_id));

		$related_sections = array_keys(array_flip($related_sections));

		if (empty($related_sections)  || !($article_data = $this->get_article_data($article_id))) {
			return;
		}

		foreach ($related_sections as $section_id) {
			$insert_data[] = array(
				'article_id' => $article_id,
				'section_id' => $section_id,
				'date_added' => 'NOW()',
			);
		}

		if (!empty($insert_data)) {
			$this->sYra_help->insert($insert_data, 'article_to_section');
		}
	}

	/**
	 * @param int $article_id
	 * @param array $excluded_resellers
	 */
	private function save_article_excluded_resellers ($article_id, $excluded_resellers) {
		$this->sYra_help->delete('sYra_help.article_exclude_reseller', "article_id = :article_id", array(':article_id' => $article_id));

		if (empty($excluded_resellers) || !($article_data = $this->get_article_data($article_id))) {
			return;
		}

		$where = 'WHERE reseller_id IN (' . implode(', ', array_fill(0, sizeof($excluded_resellers), '?')) . ')';

		$query = "	SELECT reseller_id, reseller_name
				  	FROM sYra.reseller
				  	{$where}";

		$exists_reseller_id = $this->sYra_help->query($query, $excluded_resellers)->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
		$insert_data = array();

		foreach ($excluded_resellers as $reseller_id) {
			if (empty($exists_reseller_id[$reseller_id])) {
				continue;
			}

			$insert_data[] = array(
				'article_id' => $article_id,
				'reseller_id' => $reseller_id,
				'date_added' => 'NOW()',
			);
		}

		if (!empty($insert_data)) {
			$this->sYra_help->insert($insert_data, 'sYra_help.article_exclude_reseller');
		}
	}

	/**
	 * @param array $article_ids
	 * @param int $status_id
	 * @return bool
	 */

	private function save_article_search_tags($article_id, $tags) {
		if (!($article_data = $this->get_article_data($article_id))) {
			return;
		}

		$tags = array_map('trim', $tags);

		//Delete duplicated tags, if user set some tags twice
		$tags = array_unique($tags);

		$attached_tags = $article_data['article_search_tags'];
		foreach ($attached_tags as $key => $value) {
			$attached_tags[$key] = strtolower($value);
		}

		$in_query = implode(',', array_fill(0, count($tags), '?'));
		$query = "	SELECT at.article_tag_id, at.article_tag
						FROM article_tags at
						WHERE article_tag IN ({$in_query})";

		$already_exists_tags = $this->sYra_help->query($query, array_values($tags))->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
		$already_exists_tags = array_map('reset', $already_exists_tags);

		foreach ($already_exists_tags as $key => $value) {
			$already_exists_tags[$key] = strtolower($value);
		}

		$insert_data = array();
		foreach ($tags as $tag) {
			$tag_to_check = strtolower($tag);

			if (!$tag_to_check || in_array($tag_to_check, $attached_tags)) {
				if ($tag_to_check) {
					$tag_id = reset(array_keys($attached_tags, $tag_to_check));
					unset($attached_tags[$tag_id]);
				}

				continue;
			}

			if (!in_array($tag_to_check, $already_exists_tags)) {
				$prepared_tag = $this->article_prepare_search->prepare_user_keywords($tag, array(
					'add_separate_words_from_collocation' => false,
					'only_abbreviation' => true,
					'add_quotes_to_collocations' => false,
				));

				$insert_tag_data = array(
					'article_tag' => $tag,
					'article_prepared_tag' => $prepared_tag,
					'date_added' => 'NOW()',
				);

				$this->sYra_help->insert($insert_tag_data, 'article_tags');
				$tag_id = $this->sYra_help->last_insert_id();
			} else {
				$tag_id = reset(array_keys($already_exists_tags, $tag_to_check));
			}

			$insert_data[] = array(
				'article_tag_id' => $tag_id,
				'article_id' => $article_id,
				'date_added' => 'NOW()',
			);
		}

		$this->sYra_help->insert($insert_data, 'tag_to_article', true);

		if ($attached_tags) {
			$in_query = implode(',', array_fill(0, count($attached_tags), '?'));
			$this->sYra_help->delete('tag_to_article', "article_tag_id IN ({$in_query}) AND article_id = {$article_id}", array_keys($attached_tags));
		}
	}

	public function update_status($article_ids, $status_id) {
		if (!isset($article_ids, $status_id) || !is_array($article_ids) || (intval($status_id) == 0)) {
			return false;
		}

		$ids = implode(',', $article_ids);
		$update_data = array('status_id' => $status_id);

		if ($status_id == 3) { /* Published */
			$update_data['date_published'] = 'NOW()';
		}

		$result = $this->sYra_help->update($update_data, 'article', "article_id IN ({$ids})");
		if ($result) {
			return true;
		}

		return false;
	}

	/**
	 * @param array $data
	 * @return int
	 */
	public function save_article($data) {
		if (!isset($data) || (!is_array($data))) {
			return "Array wasn't set or not array was received. ";
		}

		if (empty($data['article_title'])) {
			return 'Please, provide article title. ';
		}

		if (empty($data['article_url'])) {
			return 'Please, provide article url. ';
		}

		if (empty($data['article_description'])) {
			return 'Please, provide article description. ';
		}

		if (strlen($data['article_description']) > 130) {
			return 'Article description is too long. ';
		}

		if (empty($data['article_content'])) {
			return 'Please, provide article content. ';
		}

		if (empty($data['section_id'])) {
			return 'Please, provide article section. ';
		}

		$insert_data = array(
			'article_url' => $data['article_url'],
			'article_title' => $data['article_title'],
			'article_description' => $data['article_description'],
			'article_tags' => $data['article_tags'],
			'article_content' => $data['article_content'],
			'status_id' => $data['article_status'],
			'date_scanned' => 'NOW()',
			'doc_type' => $data['doc_type'],
			'section_id' => $data['section_id'],
		);

		if ($data['article_status'] == 3) { /* Published */
			$insert_data['date_published'] = 'NOW()';
			$insert_data['date_updated'] = 'NOW()';
		}

		//check for url duplication and return error message if duplicated article_title or article_url
		$result = $this->article_content_issues->check_article_for_duplications(0, $data['article_url'], $data['article_title']);
		if ($result !== true) {
			return $result;
		}

		$this->sYra_help->insert($insert_data, 'article');

		$article_id = $this->sYra_help->last_insert_id();

		if (is_array($data['linked_images']) && $data['linked_images']) {
			$insert_data = array();

			foreach ($data['linked_images'] as $image_id) {
				$insert_data[] = array(
					'article_id' => $article_id,
					'image_id' => $image_id,
				);
			}

			$this->sYra_help->insert($insert_data, 'image_to_article');
		}

		$this->parse_article_content($article_id, $data['article_content']);
		$this->article_prepare_search->set_article_search_string(array($article_id));

		if (isset($data['article_search_tags'])) {
			$this->save_article_search_tags($data['article_id'], explode("\n", $data['article_search_tags']));
		}
		//check for issues
		/*
		$result = $this->article_content_issues->check_current_article_for_issues($article_id);
		if (!$result) {
			return $this->article_content_issues->get_last_error();
		}
		*/
		return intval($article_id);
	}

	public function update_article($data) {
		if (!isset($data) || (!is_array($data))) {
			return "Array wasn't set or not array was received. ";
		}

		if (empty($data['article_title'])) {
			return 'Please, provide article title. ';
		}

		if (empty($data['article_url'])) {
			return 'Please, provide article url. ';
		}

		if (empty($data['article_description'])) {
			return 'Please, provide article description. ';
		}

		if (strlen($data['article_description']) > 130) {
			return 'Article description is too long. ';
		}

		if (empty($data['article_content'])) {
			return 'Please, provide article content. ';
		}

		if (empty($data['section_id'])) {
			return 'Please, provide article section. ';
		}

		if (!isset($data['article_id']) || !($data['article_id'])) {
			return 0;
		}

		$update_data = array(
			'article_url' => $data['article_url'],
			'article_title' => $data['article_title'],
			'article_description' => $data['article_description'],
			'article_tags' => $data['article_tags'],
			'article_content' => $data['article_content'],
			'status_id' => $data['article_status'],
			'section_id' => $data['section_id'],
			'doc_type' => $data['doc_type'],
			'date_updated' => 'NOW()',
		);

		if ($data['article_status'] == 3) { /* Published */
			$update_data['date_published'] = 'NOW()';
		}

		//check for url duplication
		$result = $this->article_content_issues->check_article_for_duplications($data['article_id'], $data['article_url'], $data['article_title']);
		if ($result !== true) {
			return $result;
		}

		$this->sYra_help->update($update_data, 'article', 'article_id = :article_id', array(':article_id' => $data['article_id']));

		$this->parse_article_content($data['article_id'], $data['article_content']);
		$this->article_prepare_search->set_article_search_string(array($data['article_id']));

		if (isset($data['article_search_tags'])) {
			$this->save_article_search_tags($data['article_id'], explode("\n", $data['article_search_tags']));
		}

		if (isset($data['excluded_locales'])) {
			$this->save_article_excluded_locales($data['article_id'], $data['excluded_locales']);
		}

		if (isset($data['excluded_resellers'])) {
			$this->save_article_excluded_resellers($data['article_id'], $data['excluded_resellers']);
		}

		$data['related_sections'] = !empty($data['related_sections']) ? array_merge($data['related_sections'], array($data['section_id'])) : array($data['section_id']);

		if (isset($data['related_sections'])) {
			$this->save_article_related_sections($data['article_id'], $data['related_sections']);
		}

		return intval($data['article_id']);
	}

	/**
	 * @return array|bool
	 */
	public function get_articles() {
		/* @TODO everything with pagination */
		$conditions = $offset = '';

		if (isset($_GET['search']) && isset($_GET['article_id']) && (!intval($_GET['article_id']) == 0)) {
			$article_id = preg_replace('/[^0-9]/', '', $_GET['article_id']);
			$conditions .= " AND a.article_id = {$article_id}";
		} else if (isset($_GET['page'])) {
			$offset = 15 * intval($_GET['page']) . ", ";
		} else if (isset($_GET['section_name'])) {
			$section_title = preg_replace('/\-/', ' ', $_GET['section_name']);
			$section_title = preg_replace('/[^a-zA-Z0-9 ]/', '', $section_title);
			if ($section_title == 'Getting Started') {
				$conditions .= ' AND (s.section_title = "' . $section_title . '" OR a.doc_type = "guide")';
			} else {
				$conditions .= " AND s.section_title = '{$section_title}'";
			}
		}

		$query = "	SELECT 	a.article_id, a.article_url, a.article_title, a.date_scanned, a.date_published,
						a.date_updated, a.status_id, gs.status_name as status
					FROM article a
						LEFT JOIN generic_status gs ON a.status_id = gs.status_id
						LEFT JOIN article_sections s USING (section_id)
					WHERE a.status_id != {$this->article_statuses['delete']}
						{$conditions}
						ORDER BY
							CASE
								WHEN a.date_updated != '0000-00-00 00:00:00' THEN a.date_updated
								ELSE a.date_scanned
							END
						DESC
					LIMIT {$offset} 999";
		$result = $this->sYra_help->query($query);

		return $result->fetchAll();
	}

	/**
	 * @param $article_id
	 * @return mixed
	 */
	public function get_article_data($article_id) {
		if (!isset($article_id) || (intval($article_id) == 0)) {
			return array();
		}

		$query = "	SELECT a.article_id, a.file_id, a.article_url, a.article_title, a.article_description,
						a.article_tags, a.section_id, a.article_content, a.weight, a.status_id, a.date_scanned, gs.status_name,
						a.date_published, a.date_updated, a.doc_type, asec.section_title
					FROM article a
						INNER JOIN generic_status gs USING(status_id)
						LEFT JOIN article_sections asec USING(section_id)
					WHERE article_id = :article_id";
		$data_result = $this->sYra_help->query($query, array(':article_id' => $article_id))->fetch();

		if (!$data_result) {
			return array();
		}

		$query = "	SELECT at.article_tag_id, article_tag
					FROM tag_to_article tta
						INNER JOIN article_tags at USING (article_tag_id)
					WHERE article_id = :article_id";

		$article_search_tags = $this->sYra_help->query($query, array(':article_id' => $article_id))->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
		$article_search_tags = array_map('reset', $article_search_tags);

		$query = "	SELECT value
					FROM user_feedback
					WHERE article_id = :article_id";
		$feedback_result = $this->sYra_help->query($query, array(':article_id' => $article_id))->fetchAll();

		$helpful = $unhelpful = 0;

		if ($feedback_result) {
			$feedback_result = input::filter_variables($feedback_result);
			foreach ($feedback_result as $value) {
				if ($value['value'] > 0) {
					$helpful += $value['value'];
				} else {
					$unhelpful += abs($value['value']);
				}
			}
		}

		$query = "	SELECT al.exclude_locale
					FROM sYra_help.article_locale al
					WHERE article_id = :article_id";
		$excluded_locales = $this->sYra_help->query($query, array(':article_id' => $article_id))->fetchAll(PDO::FETCH_COLUMN);

		$query = "	SELECT aer.reseller_id
					FROM sYra_help.article_exclude_reseller aer
					WHERE article_id = :article_id";
		$excluded_resellers = $this->sYra_help->query($query, array(':article_id' => $article_id))->fetchAll(PDO::FETCH_COLUMN);

		$data_result += array(
			'helpful' => $helpful,
			'unhelpful' => $unhelpful,
			'article_search_tags' => $article_search_tags,
			'excluded_locales' => $excluded_locales,
			'excluded_resellers' => $excluded_resellers,
		);

		return $data_result;
	}

	/**
	 * @param int $article_id
	 * @return array
	 */
	public function get_article_attachments($article_id) {
		if (!isset($article_id) || (intval($article_id) == 0)) {
			return array();
		}

		$query = "	SELECT  ai.image_id, atai.image_id, atai.article_id, ai.image_name, ai.mime_type, ai.image
					FROM image_to_article atai
						INNER JOIN article_images ai USING (image_id)
					WHERE article_id = :article_id
					ORDER BY ai.image_id ASC";
		$result = $this->sYra_help->query($query, array(':article_id' => $article_id));
		$output = $result->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

		$output = array_map('reset', $output);

		return $output;
	}

	/**
	 * Get related sections of article
	 *
	 * @param int $article_id
	 * @return array
	 */
	public function get_article_additional_sections($article_id) {
		if (empty($article_id)) {
			return array();
		}

		$query = "	SELECT section_id, article_id
					FROM article_to_section
					WHERE article_id = :article_id";

		$sections = $this->sYra_help->query($query, array(':article_id' => $article_id))->fetchAll(PDO::FETCH_GROUP);

		return array_keys($sections);
	}

	/**
	 * Get all statuses list
	 *
	 * @return array|bool
	 */
	public function get_statuses_list() {
		$query = "	SELECT status_id, status_name, status_color
					FROM generic_status";
		$result = $this->sYra_help->query($query);
		return $result->fetchAll();
	}

	/**
	 * Get section list with count of associated articles
	 *
	 * @return array|bool
	 */
	public function get_sections_list() {
		$query = "	SELECT  arts.section_id, arts.section_id, arts.section_title, arts.section_description,
							arts.section_icon, arts.priority, article_total.total
					FROM article_sections arts
						LEFT JOIN (
							SELECT a.section_id, COUNT(a.article_id) total
							FROM article a
							WHERE a.status_id != {$this->article_statuses['delete']}
							GROUP BY a.section_id
						) article_total USING (section_id)
					ORDER BY arts.priority ASC";
		$result = $this->sYra_help->query($query);
		$output = $result->fetchAll(PDO::FETCH_GROUP);
		$output = array_map('reset', $output);

		//Get guides and pushed count in section_id = 2
		$guide_section_id = 2;
		$query = "	SELECT COUNT(a.article_id) as total
					FROM article a
					WHERE a.status_id != {$this->article_statuses['delete']}
						AND a.section_id != {$guide_section_id}
						AND a.doc_type = 'guide' ";

		$result = $this->sYra_help->query($query)->fetch();
		$output[$guide_section_id]['total'] += $result['total'];

		return $output;
	}

	/**
	 * Save linked to article image in database
	 *
	 * @param array $data array of image info. article_id ceil to link image to article is required
	 * @return bool|int
	 */
	public function save_image($data) {
		if (!isset($data['name'], $data['content'], $data['mime_type'], $data['article_id'])) {
			return 0;
		}

		$insert_data = array(
			'image_name' => $data['name'],
			'image' => $data['content'],
			'mime_type' => $data['mime_type'],
		);

		$this->sYra_help->insert($insert_data, 'article_images');
		$image_id = $this->sYra_help->last_insert_id();

		if (($data['article_id'] != 0) && (intval($data['article_id']) != 0)) {
			$this->sYra_help->insert(array('article_id' => $data['article_id'], 'image_id' => $image_id), 'image_to_article');
		}

		return $image_id;
	}

	/**
	 * Delete image
	 *
	 * @param int $image_id
	 * @param int $article_id
	 * @return bool
	 */
	public function delete_image($image_id, $article_id) {
		if (!isset($image_id, $article_id) || (intval($image_id) == 0) || (intval($article_id) == 0)) {
			return false;
		}

		$this->sYra_help->delete('image_to_article', 'article_id = :article_id AND image_id = :image_id', array(':article_id' => $article_id, ':image_id' => $image_id));
		$this->sYra_help->delete('article_images', 'image_id = :image_id', array(':image_id' => $image_id));

		return true;
	}

	/**
	 * Get image from sYra_help db article_image table.
	 *
	 * @param (int) $image_id
	 * @return mixed
	 */
	public function get_image($image_id) {
		if ((!isset($image_id)) || (intval($image_id) == 0)) {
			return array();
		}

		$query = "	SELECT image_id, image_name, mime_type, image
					FROM article_images
					WHERE image_id = :image_id";
		$result = $this->sYra_help->query($query, array(':image_id' => $image_id));

		return $result->fetch();
	}

	/**
	 * @param array $data
	 * @return bool
	 * TODO :: change using of the article_tags to section_id. This field should be deleted. No sense in this method any more...
	 */
	public function check_doc_type($data) {
/**
	if (!isset($data) || (!is_array($data))) {
			return false;
		}


		if ($data['doc_type'] == 'guide') {
			$query = "	SELECT 1
						FROM article
						WHERE doc_type = :doc_type
							AND article_tags = :article_tags
							AND article_id != :article_id";
			return $this->sYra_help->query($query, array(':doc_type' => $data['doc_type'],	':article_tags' => $data['article_tags'], ':article_id' => $data['article_id']))->fetch() ? true : false;
		}
*/
		return false;
	}

	/**
	 * Replace src attribute if <img> html tag in articles content.
	 * This method usable if preview page is generates in this help section tool
	 *
	 * @param string $content
	 * @param int $article_id
	 * @return mixed
	 */
	public function preview_image_src_replace($content, $article_id = 0) {
		preg_match_all("/src=\"([^\/\\\\]+(.jpg|.jpeg|.png|.gif))\"/", $content, $matches);

		if ($matches[0]) {
			if ($article_id) {
				$attachments = $this->get_article_attachments($article_id);
				foreach ($attachments as $image_id => $image) {
					$attachments[$image_id] = $image['image_name'];
				}
				$attachments = array_flip($attachments);
			} else {
				$attachments = false;
			}

			foreach ($matches[0] as $key => $img_url) {
				if (!$attachments || in_array($matches[1][$key], array_keys($attachments))) {
					$pattern = "/" . $img_url . "/";
					$replacement = 'src="http://' . $_SERVER['SERVER_NAME'] . ARTICLE_EDITOR_ROOT_PATH .'get_image?id=' . $attachments[$matches[1][$key]] . '"';

					$content = preg_replace($pattern, $replacement, $content);
				}
			}
		}

		return $content;
	}

	/**
	 * Uploading files(images) from html-form and pushing it into sYra_help db
	 *
	 * @param array $files_stack
	 * @return array
	 */
	public function upload_images($files_stack) {
		if (empty($files_stack)) {
			return array('success' => 'Form was submitted', 'formData' => input::all());
		}

		$error = false;
		$files = array();

		$allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif');

		foreach ($files_stack as $file) {
			if (in_array($file['type'], $allowed_mime_types)) {
				if (move_uploaded_file($file['tmp_name'], UPLOAD_DIR . basename($file['name']))) {

					$data = array(
						'name' => $file['name'],
						'mime_type' => $file['type'],
						'content' => file_get_contents(UPLOAD_DIR . basename($file['name'])),
						'article_id' => input::get('article_id'),
					);

					$image_id = $this->save_image($data);

					$files[$image_id] = array(
						'image_id' => $image_id,
						'image_name' => $file['name'],
					);

					unlink(UPLOAD_DIR . basename($file['name']));
				} else {
					$error = true;
				}
			}
		}

		$data = ($error) ? array('error' => 'There was an error uploading your files') : array('files' => $files);

		return $data;
	}

    public function get_articles_untagged_count_dev($count)
    {
        return [
            'status_id' => '1005',
            'status_name' => 'Missing Tags',
            'status_count' => $count,
            'status_color' => '',
        ];
	}

    public function get_articles_tagged_count_dev()
    {
        $query = "
            select 
                '1004' as status_id, 'tagged' as status_name, 'black' as status_color,
                count(distinct a.article_id) as status_count
            from tag_to_article tta
                left join article a on tta.article_id = a.article_id
            where a.status_id != {$this->article_statuses['delete']}
        ";
        return $this->sYra_help->query($query)->fetch();
	}

    public function get_article_comments_count_dev() {
        $query = "SELECT 1001 as status_id, 'Comments' as status_name, '#9E15E1' as status_color, COUNT(comment_id) as status_count FROM article_comments;";
        return $this->sYra_help->query($query)->fetch();
    }

	public function get_articles_statuses_amount() {
		$query = "	SELECT
						(
							SELECT COUNT(article_id) AS finished
							FROM article
							WHERE status_id = 1
						) as finished,
						(
							SELECT COUNT(article_id) AS published
							FROM article
							WHERE status_id = 3
						) as published,
						(
							SELECT COUNT(article_id) AS hold
							FROM article
							WHERE status_id = 4
						) as hold,
						(
							SELECT COUNT(article_id) AS unpublished
							FROM article
							WHERE status_id = 5
						) as unpublished,
						(
							SELECT COUNT(feedback_id) AS helpful
							FROM user_feedback
							WHERE value = 1
						) as helpful,
						(
						SELECT COUNT(feedback_id) AS unhelpful
							FROM user_feedback
							WHERE value = -1
						) as unhelpful,
						(
						SELECT COUNT(DISTINCT a.article_id) AS tagged
							FROM tag_to_article tta
								LEFT JOIN article a ON tta.article_id = a.article_id
							WHERE a.status_id != {$this->article_statuses['delete']}
						) as tagged
					FROM article
					LIMIT 1";
		$result = $this->sYra_help->query($query);
		$articles_statuses_amount = $result->fetch();

		return $articles_statuses_amount;
	}

	public function get_new_articles($sort_of_article = 'new', $limit = 0) {
		if (!isset($limit) || ($limit == 0) || (intval($limit == 0))) {
			$limit = '';
		} else {
			$limit = 'LIMIT ' . $limit;
		}

		if ($sort_of_article == 'new') {
			$condition = 'AND a.date_scanned >= (CURRENT_DATE - INTERVAL 1 MONTH)';
			$order_by = 'a.date_scanned';
		} else if ($sort_of_article == 'last') {
			$condition = 'AND a.date_updated >= (CURRENT_DATE - INTERVAL 1 MONTH)';
			$order_by = 'a.date_updated';
		} else {
			$condition = '';
			$order_by = 'a.date_scanned';
		}

		$query = "	SELECT a.article_id, a.article_url, a.article_title, a.date_scanned, a.date_published,
						a.date_updated, a.status_id, gs.status_name as status
					FROM article a
						LEFT JOIN generic_status gs ON a.status_id = gs.status_id
						LEFT JOIN article_sections s USING (section_id)
					WHERE a.status_id != {$this->article_statuses['delete']}
						{$condition}
						ORDER BY {$order_by} DESC
					{$limit}";
		$result = $this->sYra_help->query($query);
		$new_articles = $result->fetchAll();

		return $new_articles;
	}

	public function get_articles_with_missing_tags() {
		$query = "	SELECT a.article_id, a.article_url, a.article_title, a.date_scanned, a.date_published,
						a.date_updated, a.status_id, gs.status_name as status
					FROM article a
						LEFT JOIN generic_status gs ON a.status_id = gs.status_id
						LEFT JOIN article_sections s USING (section_id)
						LEFT JOIN tag_to_article tta ON tta.article_id = a.article_id
					WHERE a.status_id != {$this->article_statuses['delete']}
						AND tta.article_id IS NULL
					ORDER BY a.article_id DESC";
		$result = $this->sYra_help->query($query);
		$missing_tags_articles = $result->fetchAll();

		return $missing_tags_articles;
	}

	public function get_articles_by_status($status_name) {
		$status_name  = !empty($this->article_statuses[$status_name]) ? $status_name : 'published';
		$status_id = $this->article_statuses[$status_name];

		$query = "	SELECT a.article_id, a.article_url, a.article_title, a.date_scanned, a.date_published,
						a.date_updated, a.status_id, gs.status_name as status
					FROM article a
						LEFT JOIN generic_status gs ON a.status_id = gs.status_id
						LEFT JOIN article_sections s USING (section_id)
					WHERE a.status_id = :status_id";
		$data_result = $this->sYra_help->query($query, array(':status_id' => $status_id))->fetchAll(PDO::FETCH_ASSOC);
		$this->reorder_output_results($data_result, 'date_updated');

		return $data_result;
	}

	/**
	 * Get login details of moderator, that have permissions for moderating comments directly on members area
	 *
	 * @param $crms_user_id
	 * @param $member_id
	 * @return array
	 */
	public function get_comment_moderator_login_details ($crms_user_id, $member_id) {
		$allowed_members = environment::is_production() ? array() : array(7165901);
		$allowed_crms_users = environment::is_production() ? array() : array(3187);

		$default_user = array('member_id' => 0, 'reseller_id' => -1, 'username' => '', 'password' => '');

		if (!in_array($member_id, $allowed_members) || !in_array($crms_user_id, $allowed_crms_users)) {
			return $default_user;
		}

		$query = "	SELECT member_id, reseller_id
					FROM sYra.members
					WHERE member_id = :member_id";

		$member_details = $this->sYra_help->query($query, array(':member_id' => $member_id))->fetch();

		$query = "	SELECT username, password, full_name
				  	FROM sYra.crms_users
				  	WHERE user_id = :user_id";

		$crms_details = $this->sYra_help->query($query, array(':user_id' => $crms_user_id))->fetch();

		if (!$member_details || !$crms_details) {
			return $default_user;
		}

		return array(
			'member_id' => $member_details['member_id'],
			'reseller_id' => $member_details['reseller_id'],
			'username' => $crms_details['username'],
			'password' => $crms_details['password'],
		);

	}

	/**
	 * Get all comment statuses
	 *
	 * @return array
	 */
	private function get_comment_available_statuses() {
		$query = "	SELECT status_name, status_id
					FROM article_comments_statuses";

		$statuses = $this->sYra_help->query($query)->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
		return array_map('reset', $statuses);
	}

	/**
	 * Get total count of comments
	 *
	 * @return string
	 */
	public function get_article_comments_count() {
		$query = 'SELECT COUNT(comment_id) FROM article_comments';
		return $this->sYra_help->query($query)->fetchColumn();
	}

	/**
	 * Get comments by specified filter
	 *
	 * @param array $filter
	 * @return array
	 */
	public function get_comments(array $filter = array()) {
		$where = array();
		$bind = array();

		if (!empty($filter['status'])) {
			$available_statuses = $this->get_comment_available_statuses();

			//status was set incorrect
			if (empty($available_statuses[$filter['status']])) {
				return array();
			}

			$where[] = 'ac.status_id = :status_id';
			$bind[':status_id'] = $available_statuses[$filter['status']];
		}

		$where = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

		$query = "	SELECT 	ac.comment_id, ac.comment_id, ac.article_id, ac.message, ac.date_added, ac.date_published,
							IFNULL(acac.display_name, aca.display_name) display_name,
							IFNULL(acac.profile_image_url, aca.profile_image_url) profile_image_url,
							acs.status_id, acs.status_name, acs.status_color,
							a.article_url, a.article_title
					FROM article_comments ac
						INNER JOIN article_comments_author aca ON ac.author_id = aca.author_id
						INNER JOIN article_comments_statuses acs ON ac.status_id = acs.status_id
						INNER JOIN article a ON ac.article_id = a.article_id
						LEFT JOIN sYra_help.article_comments_author_customisation acac ON acac.author_id = aca.author_id
					{$where}
					ORDER BY ac.date_added DESC";

		$comments = $this->sYra_help->query($query, $bind)->fetchAll(PDO::FETCH_GROUP);
		$comments = array_map('reset', $comments);

		return $comments;
	}

	private function reorder_output_results(&$data_result, $order_field) {
		$reorderded_output_results = array();
		foreach ($data_result as $key => $row) {
			$reorderded_output_results[$key] = $row[$order_field];
		}

		array_multisort($reorderded_output_results, SORT_DESC, $data_result);
	}
}
