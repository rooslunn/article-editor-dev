<?php


namespace Dreamscape\Repository;


final class SectionRepository extends Repository
{
    use WithArticleStatuses;

    private function queryAll()
    {
        $query = "	SELECT  arts.section_id, arts.section_id, arts.section_title, arts.section_description,
							arts.section_icon, arts.priority, article_total.total
					FROM article_sections arts
						LEFT JOIN (
							SELECT a.section_id, COUNT(a.article_id) total
							FROM article a
							WHERE a.status_id != {$this->articleStatusId('delete')}
							GROUP BY a.section_id
						) article_total USING (section_id)
					ORDER BY arts.priority ASC";
        return $query;
    }

    public function get()
    {
        $this->fetchAll($this->queryAll());
    }
}