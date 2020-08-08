<?php

namespace Yonna\QuickStart\Scope\Essay;

use Yonna\QuickStart\Helper\Assets;
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
    public function multi(): array
    {
        $prism = new EssayPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getTitle() && $w->like('title', '%' . $prism->getTitle() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getCategoryId() && $w->equalTo('category_id', $prism->getCategoryId());
            })
            ->orderBy('sort', 'desc')
            ->multi();
    }

    /**
     * @return mixed
     * @throws Exception\DatabaseException
     */
    public function page(): array
    {
        $prism = new EssayPrism($this->request());
        return DB::connect()->table(self::TABLE)
            ->where(function (Where $w) use ($prism) {
                $prism->getTitle() && $w->like('title', '%' . $prism->getTitle() . '%');
                $prism->getStatus() && $w->equalTo('status', $prism->getStatus());
                $prism->getCategoryId() && $w->equalTo('category_id', $prism->getCategoryId());
            })
            ->orderBy('sort', 'desc')
            ->page($prism->getCurrent(), $prism->getPer());
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
        $content = Assets::getHtmlSource($this->input('content') ?? '');
        $data = [
            'user_id' => $this->request()->getLoggingId(),
            'title' => $this->input('title'),
            'category_id' => $this->input('category_id') ?? 0,
            'status' => $this->input('status') ?? EssayCategoryStatus::PENDING,
            'likes' => $this->input('likes') ?? 0,
            'views' => $this->input('views') ?? 0,
            'content' => $content,
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
        $content = Assets::getHtmlSource($this->input('content') ?? null);
        $data = [
            'title' => $this->input('title'),
            'category_id' => $this->input('category_id'),
            'status' => $this->input('status'),
            'likes' => $this->input('likes'),
            'views' => $this->input('views'),
            'content' => $content,
            'sort' => $this->input('sort'),
        ];
        if ($data) {
            return DB::connect()->table(self::TABLE)
                ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
                ->update($data);
        }
        return true;
    }

}