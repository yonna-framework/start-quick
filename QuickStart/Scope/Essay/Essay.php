<?php

namespace Yonna\QuickStart\Scope\Essay;

use Yonna\QuickStart\Mapping\Essay\EssayCategoryStatus;
use Yonna\QuickStart\Prism\EssayPrism;
use Yonna\QuickStart\Scope\AbstractScope;
use Yonna\Database\DB;
use Yonna\Database\Driver\Pdo\Where;
use Yonna\Throwable\Exception;
use Yonna\Validator\ArrayValidator;

/**
 * Class Essay
 * @package Yonna\QuickStart\Scope\Essay
 */
class Essay extends AbstractScope
{

    const TABLE = 'essay';

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function one(): array
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $info = DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->one();
        $info['essay_content'] = $this->xoss_load($info['essay_content']);
        return $info;
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function multi(): array
    {
        $prism = new EssayPrism($this->request());
        $list = DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getTitle() && $w->like('title', '%' . $prism->getTitle() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getCategoryId() && $w->equalTo('category_id', $prism->getCategoryId());
            })
            ->orderBy('sort', 'desc')
            ->multi();
        foreach ($list as $lk => &$l) {
            $l['essay_content'] = $this->xoss_load($l['essay_content']);
        }
        return $list;
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function page(): array
    {
        $prism = new EssayPrism($this->request());
        $page = DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getId() && $w->equalTo('id', $prism->getId());
                $prism->getTitle() && $w->like('title', '%' . $prism->getTitle() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getCategoryId() && $w->equalTo('category_id', $prism->getCategoryId());
            })
            ->orderBy('sort', 'desc')
            ->page($prism->getCurrent(), $prism->getPer());
        foreach ($page['list'] as $lk => &$l) {
            $l['essay_content'] = $this->xoss_load($l['essay_content']);
        }
        return $page;
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function insert()
    {
        ArrayValidator::required($this->input(), ['title', 'category_id'], function ($error) {
            Exception::throw($error);
        });
        $content = $this->xoss_save($this->input('content') ?? '');
        $data = [
            'user_id' => $this->request()->getLoggingId(),
            'title' => $this->input('title'),
            'category_id' => $this->input('category_id') ?? 0,
            'status' => $this->input('status') ?? EssayCategoryStatus::PENDING,
            'likes' => $this->input('likes') ?? 0,
            'views' => $this->input('views') ?? 0,
            'content' => $content,
            'author' => $this->input('author') ?? 0,
            'publish_time' => $this->input('publish_time') ?? time(),
            'sort' => $this->input('sort') ?? 0,
        ];
        return DB::connect()->table(self::TABLE)->insert($data);
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function update()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        $content = $this->xoss_save($this->input('content') ?? null);
        $data = [
            'title' => $this->input('title'),
            'category_id' => $this->input('category_id'),
            'status' => $this->input('status'),
            'likes' => $this->input('likes'),
            'views' => $this->input('views'),
            'content' => $content,
            'author' => $this->input('author'),
            'publish_time' => $this->input('publish_time'),
            'sort' => $this->input('sort'),
        ];
        if ($data) {
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
                ->update($data);
        }
        return true;
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function delete()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->delete();
    }

}