<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Events;

defined('C5_EXECUTE') or die('Access Denied.');

class GenerateApiGuideEvent
{
    /**
     * The markdown of the guide, with the placeholders still in it.
     *
     * @var string
     */
    protected $markdown;

    /**
     * The values that the placeholders are replaced with, keyed by the name of the placeholder
     * (without the percent signs that delimit it in the markdown).
     *
     * @var array<string,string>
     */
    protected $placeholders;

    /**
     * @param string $markdown the markdown of the guide, with the placeholders still in it
     * @param array<string,string> $placeholders the values of the placeholders, keyed by their name
     */
    public function __construct(string $markdown, array $placeholders)
    {
        $this->markdown = $markdown;
        $this->placeholders = $placeholders;
    }

    public function getMarkdown(): string
    {
        return $this->markdown;
    }

    /**
     * @return $this
     */
    public function setMarkdown(string $markdown): self
    {
        $this->markdown = $markdown;

        return $this;
    }

    /**
     * @return array<string,string>
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * @param array<string,string> $placeholders
     *
     * @return $this
     */
    public function setPlaceholders(array $placeholders): self
    {
        $this->placeholders = $placeholders;

        return $this;
    }
}
