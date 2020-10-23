<?php

namespace Yonna\QuickStart\Prism;


use Yonna\IO\Prism;

class EssayPrism extends Prism
{

    protected int $current = 1;
    protected int $per = 10;
    protected ?int $id = null;
    protected ?array $ids = null;
    protected ?int $category_id = null;
    protected ?string $title = null;
    protected ?int $status = null;
    protected ?bool $is_excellent = null;

    /**
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     * @param int $current
     */
    public function setCurrent(int $current): void
    {
        $this->current = $current;
    }

    /**
     * @return int
     */
    public function getPer(): int
    {
        return $this->per;
    }

    /**
     * @param int $per
     */
    public function setPer(int $per): void
    {
        $this->per = $per;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return array|null
     */
    public function getIds(): ?array
    {
        return $this->ids;
    }

    /**
     * @param array|null $ids
     */
    public function setIds(?array $ids): void
    {
        $this->ids = $ids;
    }

    /**
     * @return int|null
     */
    public function getCategoryId(): ?int
    {
        return $this->category_id;
    }

    /**
     * @param int|null $category_id
     */
    public function setCategoryId(?int $category_id): void
    {
        $this->category_id = $category_id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int|null $status
     */
    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return bool|null
     */
    public function getIsExcellent(): ?bool
    {
        return $this->is_excellent;
    }

    /**
     * @param bool|null $is_excellent
     */
    public function setIsExcellent(?bool $is_excellent): void
    {
        $this->is_excellent = $is_excellent;
    }

}