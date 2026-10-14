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
     * A field holding the ID of an attribute key.
     *
     * @var string
     */
    public const ATTRIBUTE_KEY = 'attribute-key';

    /**
     * A field holding the ID of an instance of a board.
     *
     * @var string
     */
    public const BOARD_INSTANCE = 'board-instance';

    /**
     * A field holding the ID of a calendar.
     *
     * @var string
     */
    public const CALENDAR = 'calendar';

    /**
     * A field holding the ID of an event of a calendar.
     *
     * @var string
     */
    public const CALENDAR_EVENT = 'calendar-event';

    /**
     * A field holding HTML, which may refer to pages and files of the site.
     *
     * @var string
     */
    public const CONTENT = 'content';

    /**
     * A field holding the UUID of an Express entity.
     *
     * @var string
     */
    public const EXPRESS_ENTITY = 'express-entity';

    /**
     * A field holding the ID of an entry of an Express entity.
     *
     * @var string
     */
    public const EXPRESS_ENTRY = 'express-entry';

    /**
     * A field holding the UUID of a form of an Express entity.
     *
     * @var string
     */
    public const EXPRESS_FORM = 'express-form';

    /**
     * A field holding the ID of a control of a form of an Express entity.
     *
     * @var string
     */
    public const EXPRESS_FORM_CONTROL = 'express-form-control';

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
     * A field holding the ID of a set of files.
     *
     * @var string
     */
    public const FILE_SET = 'file-set';

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

    /**
     * A field holding the ID of a site of this installation.
     *
     * @var string
     */
    public const SITE = 'site';

    /**
     * A field holding the ID of a social link of the site.
     *
     * @var string
     */
    public const SOCIAL_LINK = 'social-link';

    /**
     * A field holding the ID of a stack.
     *
     * @var string
     */
    public const STACK = 'stack';

    /**
     * A field holding the ID of a thumbnail type.
     *
     * @var string
     */
    public const THUMBNAIL_TYPE = 'thumbnail-type';

    /**
     * A field holding the ID of the node of a topic.
     *
     * @var string
     */
    public const TOPIC = 'topic';

    /**
     * A field holding the ID of a tree of topics.
     *
     * @var string
     */
    public const TOPIC_TREE = 'topic-tree';

    /**
     * A field holding the ID of a user.
     *
     * @var string
     */
    public const USER = 'user';

    /**
     * A field holding the ID of a group of users.
     *
     * @var string
     */
    public const USER_GROUP = 'user-group';

    /**
     * The kinds of reference that a CIF file translates.
     *
     * @var string[]
     */
    private const TRANSLATED_IN_CIF = [
        self::CONTENT,
        self::FILE,
        self::FILE_FOLDER,
        self::PAGE,
        self::PAGE_FEED,
        self::PAGE_TYPE,
    ];

    /**
     * Whether a value of this kind of reference travels in a CIF file as a {ccm:export:...}
     * placeholder instead of the ID it holds, which the export of a block type turns it into and
     * the import resolves: the other kinds only tell the clients of the API what a field names.
     *
     * @param string $reference one of the ReferenceKind::... constants, or an empty string
     */
    public static function isTranslatedInCif(string $reference): bool
    {
        return in_array($reference, self::TRANSLATED_IN_CIF, true);
    }
}
