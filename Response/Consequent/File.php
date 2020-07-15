<?php

namespace Yonna\Response\Consequent;

/**
 * Class File
 * @package Yonna\Response\Consequent
 */
class File
{

    private ?string $raw;
    private ?string $content_type;
    private ?string $name;

    public function __construct(?string $rawData, ?string $contentType, ?string $name)
    {
        $this->raw = $rawData;
        $this->content_type = $contentType ?? 'application/octet-stream';
        $this->name = $name ?? 'file-' . time();
    }

    /**
     * @return string|null
     */
    public function getRaw(): ?string
    {
        return $this->raw;
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->content_type;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

}