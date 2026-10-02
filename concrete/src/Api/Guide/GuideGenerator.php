<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Guide;

use Concrete\Core\Api\Events\GenerateApiGuideEvent;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Events\EventDispatcher;
use Illuminate\Filesystem\Filesystem;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Builds the guide of the API: the markdown document that tells an API client what the OpenAPI
 * specification of the API can't tell.
 *
 * The markdown is read from the guide.md file in the assets of the API, the %placeholders% in it are
 * replaced with the values of this installation, and the listeners of the event can have the last
 * word on both.
 */
class GuideGenerator
{
    /**
     * The name of the event dispatched while the guide is being generated.
     *
     * @var string
     *
     * @see \Concrete\Core\Api\Events\GenerateApiGuideEvent
     */
    public const EVENTNAME_GENERATE = 'on_api_guide_generate';

    /**
     * @var \Concrete\Core\Events\EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Concrete\Core\Api\Guide\PlaceholderFactory
     */
    protected $placeholderFactory;

    public function __construct(EventDispatcher $eventDispatcher, Filesystem $filesystem, PlaceholderFactory $placeholderFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->filesystem = $filesystem;
        $this->placeholderFactory = $placeholderFactory;
    }

    /**
     * Build the guide of the API.
     *
     * @throws \Concrete\Core\Error\UserMessageException when the markdown file can't be read
     */
    public function getGuide(): string
    {
        $event = new GenerateApiGuideEvent($this->getMarkdown(), $this->placeholderFactory->getPlaceholders());
        $this->eventDispatcher->dispatch(static::EVENTNAME_GENERATE, $event);

        return $this->replacePlaceholders($event->getMarkdown(), $event->getPlaceholders());
    }

    /**
     * Read the markdown of the guide, placeholders included.
     *
     * @throws \Concrete\Core\Error\UserMessageException when the markdown file can't be read
     */
    protected function getMarkdown(): string
    {
        $file = $this->getMarkdownFile();
        if (!$this->filesystem->exists($file)) {
            throw new UserMessageException(t('Unable to find the guide of the API.'));
        }

        return (string) $this->filesystem->get($file);
    }

    /**
     * Get the full path to the markdown file of the guide.
     */
    protected function getMarkdownFile(): string
    {
        return dirname(__DIR__) . '/assets/guide.md';
    }

    /**
     * @param array<string,string> $placeholders
     */
    protected function replacePlaceholders(string $markdown, array $placeholders): string
    {
        $search = [];
        $replace = [];
        foreach ($placeholders as $name => $value) {
            $search[] = "%{$name}%";
            $replace[] = (string) $value;
        }

        return str_replace($search, $replace, $markdown);
    }
}
