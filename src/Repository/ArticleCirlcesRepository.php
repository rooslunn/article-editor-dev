<?php 
namespace Dreamscape\Repository;


final class ArticleCirlcesRepository extends Repository
{
    use WithArticleStatuses;

    const CIRCLE_TAGGED = 1000;
    const CIRCLE_UNTAGGED = 1001;
    const CIRCLE_COMMENT = 1002;

    private $CALCULATED_CIRCLES = [
        self::CIRCLE_TAGGED => ['status_id' => self::CIRCLE_TAGGED, 'status_name' => 'Tagged', 'status_color' => 'black'],
        self::CIRCLE_UNTAGGED => ['status_id' => self::CIRCLE_UNTAGGED, 'status_name' => 'Missed Tags', 'status_color' => ''],
        self::CIRCLE_COMMENT => ['status_id' => self::CIRCLE_COMMENT, 'status_name' => 'Comments', 'status_color' => '#9E15E1'],
    ];

    public function item(array $init_valus = [])
    {
        $item = [
            'status_id' => null,
            'status_name' => null,
            'status_count' => null,
            'status_color' => null,
        ];
        foreach (array_keys($init_valus) as $key) {
            if (array_key_exists($key, $item)) {
                $item[$key] = $init_valus[$key];
            }
        }
        
        return $item;
    }

    private function byStatus()
    {
        $query = "
            select
                a.status_id,
                gs.status_name,
                gs.status_color,
                count(a.status_id) as status_count
            from
                article a left join generic_status gs on a.status_id = gs.status_id
            where
                a.status_id != {$this->articleStatusId('delete')}
            group by
                a.status_id,
                gs.status_name,
                gs.status_color;
        ";

        $result = array_map(function ($item) {
            return $this->item($item);
        }, $this->db()->query($query)->fetchAll());

        return $result;
    }

    private function tagged()
    {
        $circle_titles = $this->CALCULATED_CIRCLES[self::CIRCLE_TAGGED];
        $query = "
            select 
                {$circle_titles['status_id']} as status_id, 
                {$circle_titles['status_name']} as status_name, 
                {$circle_titles['status_color']} as status_color,
                count(distinct a.article_id) as status_count
            from tag_to_article tta
                left join article a on tta.article_id = a.article_id
            where a.status_id != {$this->articleStatusId('delete')}
        ";
        $result = $this->db()->query($query)->fetch();
        return $this->item($result);
    }

    private function untagged($count)
    {
        $circle_titles = $this->CALCULATED_CIRCLES[self::CIRCLE_UNTAGGED];
        return $this->item([
            'status_id' => $circle_titles['status_id'],
            'status_name' => $circle_titles['status_name'],
            'status_count' => $count,
            'status_color' => $circle_titles['status_color'],
        ]);
    }

    private function comments()
    {
        $circle_titles = $this->CALCULATED_CIRCLES[self::CIRCLE_COMMENT];
        $query = "
            select 
                {$circle_titles['status_id']} as status_id, 
                {$circle_titles['status_name']} as status_name, 
                {$circle_titles['status_color']} as status_color, 
                count(comment_id) as status_count 
            from article_comments;";
        $result = $this->sYra_help->query($query)->fetch();
        return $this->item($result);
    }

    public function all()
    {
        $circles = $this->byStatus();
        $current_count = array_sum(array_pluck($circles, 'status_count'));

        $tagged_count = $this->tagged()['status_count'];
        $circles[] = $this->untagged($current_count - $tagged_count);

        $circles[] = $this->comments();

        return $circles;
    }
}