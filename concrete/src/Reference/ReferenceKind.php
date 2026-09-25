<?php

declare(strict_types=1);

namespace Concrete\Core\Reference;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The kinds of thing of the site that a field may name instead of holding a value of its own.
 *
 * @see \Concrete\Core\Block\ReferenceColumns
 */
final class ReferenceKind
{
    /**
     * A field holding rich text, which may refer to pages and files of the site.
     *
     * @var string
     */
    public const CONTENT = 'content';

    /**
     * A field holding the ID of a file.
     *
     * @var string
     */
    public const FILE = 'file';

    /**
     * A field holding the ID of a folder of files.
     *
     * @var string
     */
    public const FILE_FOLDER = 'file-folder';

    /**
     * A field holding the ID of a page.
     *
     * @var string
     */
    public const PAGE = 'page';

    /**
     * A field holding the ID of an RSS feed of pages.
     *
     * @var string
     */
    public const PAGE_FEED = 'page-feed';

    /**
     * A field holding the ID of a page type.
     *
     * @var string
     */
    public const PAGE_TYPE = 'page-type';
}
