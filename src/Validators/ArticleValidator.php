<?php


namespace Dreamscape\Validators;


use Dreamscape\Contracts\Validators\EntityValidator;

class ArticleValidator implements EntityValidator
{

    public static function check($entity_id)
    {
        return (new \help_section\article_content_issues())->check_current_article_for_issues($entity_id);
    }
}