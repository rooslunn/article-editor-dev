/*
 Speed-up performance: article to tag_to_article JOINs
 */
create index tag_to_article__article_id on tag_to_article (article_id);
