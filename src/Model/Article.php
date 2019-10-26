<?php


namespace Dreamscape\Model;


use Dreamscape\Contracts\Database\Model as ModelContract;
use Dreamscape\Repository\ArticleImagesRepository;
use Dreamscape\Repository\ArticleToSectionRepository;

final class Article implements ModelContract
{
    const DATES = ['date_scanned', 'date_published', 'date_updated'];
    
    private static function stub()
    {
        return [
            'section_name' => 'Category',
            'article_tags' => 'Category',
            'article_id' => 0,
            'article_title' => 'Title',
            'article_input_title' => '',
            'article_url' => '',
            'article_description' => '',
            'article_content' => '',
            'article_images' => [],
            'related_sections' => [],
            'excluded_locales' => [],
            'excluded_resellers' => [],
            'action' => self::MODEL_SAVE_ACTION,
        ];
    }

    private static function convertDates(array &$data)
    {
        foreach (self::DATES as $field_name) {
            if ($data[$field_name] === self::MYSQL_ZERO_DATE) {
                $data[$field_name] = self::DS_ZERO_DATE; 
            } else {
                $data[$field_name] = date('j M Y', strtotime($data[$field_name]));
            }
        }
    }

    private static function afterCreate(&$data)
    {
        $data['action'] = self::MODEL_UPDATE_ACTION;

        /* todo: I don't know WTF this for... */
        $data['article_tags'] = $data['section_title'] ?: $data['article_tags'];
        $data['section_name'] = str_replace(' ', '-', $data['article_tags']);
        $data['article_tags'] .= !$data['section_title'] ? '(uncategorized)' : '';

        self::convertDates($data);

        $data['article_input_title'] = $data['article_title'];
        $data['search_tags_count'] = count($data['article_search_tags']);
        $data['article_search_tags'] = implode("\n", $data['article_search_tags']);

        $data['related_sections'] = (new ArticleToSectionRepository())->belongsToArtcile($data['article_id']);
        $data['article_images'] = (new ArticleImagesRepository())->belongsToArtcile($data['article_id']);
    }

    public static function create(array $data)
    {
        if ((int) $data === 0) {
            return self::stub();
        }

        self::afterCreate($data);

        return $data;
    }
}