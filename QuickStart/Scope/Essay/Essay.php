<?php

namespace Yonna\QuickStart\Scope\Essay;

use Yonna\QuickStart\Helper\Assets;
use Yonna\QuickStart\Mapping\Essay\EssayStatus;
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
            ->orderBy('id', 'desc')
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
    public function pic(): array
    {
        $pic = [];
        $list = $this->multi();
        foreach ($list as $v) {
            if (!isset($pic[$v['essay_category_id']])) {
                $pic[$v['essay_category_id']] = [];
            }
            $essay_content = $v['essay_content'];
            preg_match_all('/<img(.*?)src="(.*?)"(.*?)>/', $essay_content, $imgs);
            if ($imgs[2]) {
                foreach ($imgs[2] as $img) {
                    if (in_array($img, [
                        // 跳过一些不好看的图片
                        'http://pcenter-pio.local.cn:3001?scope=xoss_download&k=1ebc6cdeaffea83a142d7d790db9a201ad564d729d98c0f63e6ff85ac7301c576f255776',
                        'http://pcenter-pio.local.cn:3001?scope=xoss_download&k=2da5d23cc0205c3af8d7713623f26a4bb00f9e848f01e7c5a6967e7411638727755453bb',
                    ])) {
                        continue;
                    }
                    $pic[$v['essay_category_id']][] = $img;
                }
            }
        }
        foreach ($pic as &$p) {
            $p = array_unique($p);
            sort($p);
        }
        return $pic;
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
            ->orderBy('id', 'desc')
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
            'status' => $this->input('status') ?? EssayStatus::DISABLED,
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

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function views()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->update([
                'views' => ['exp', '`views`+1']
            ]);
    }

    /**
     * @return int
     * @throws Exception\DatabaseException
     */
    public function likes()
    {
        ArrayValidator::required($this->input(), ['id'], function ($error) {
            Exception::throw($error);
        });
        return DB::connect()->table(self::TABLE)
            ->where(fn(Where $w) => $w->equalTo('id', $this->input('id')))
            ->update([
                'likes' => ['exp', '`likes`+1']
            ]);
    }

}