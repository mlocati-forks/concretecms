<?php

declare(strict_types=1);

namespace Concrete\Core\Api;

use Concrete\Core\Reference\ReferenceKind;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * What the clients of the API are told about the kinds of thing a field of a value names, the ones
 * the x-concrete-reference of a field carries.
 *
 * A kind is a plain string, so a package declares one of its own on the columns of its block types
 * without a constant for it: to have it described and listed, the package binds a class of its own
 * here with $app->extend() or $app->bind(), answering for its kinds and calling parent for the rest.
 *
 * @see \Concrete\Core\Api\Controller\System::references()
 * @see \Concrete\Core\Api\Block\BlockApiHandler::describeReference()
 * @see \Concrete\Core\Block\ReferenceColumns::with()
 */
class ReferenceCatalog
{
    /**
     * How a field naming a file says which one, described to the clients of the API.
     *
     * @var string
     */
    private const FILE_FORMAT = 'Files are named by UUID, or by ID when the UUID is missing.';

    /**
     * Where a client reads what an Express entity offers, described to the clients of the API.
     *
     * @var string
     */
    private const ENTITY_FORMAT = 'GET /express_entities names the entities this API serves, each with its columns, filters, associations and forms.';

    /**
     * The endpoint handing over the things of every kind, by the kind: a kind whose things come
     * inside another answer, or through a path of their own entity, has none.
     *
     * @var array<string,string>
     */
    private const ENDPOINTS = [
        ReferenceKind::ATTRIBUTE_KEY => '/attribute_categories/{categoryHandle}/keys',
        ReferenceKind::BOARD_INSTANCE => '/boards/{boardID}/instances',
        ReferenceKind::CALENDAR => '/calendars',
        ReferenceKind::CALENDAR_EVENT => '/calendars/{calendarID}/events',
        ReferenceKind::CONTENT => '',
        ReferenceKind::EXPRESS_ENTITY => '/express_entities',
        ReferenceKind::EXPRESS_ENTRY => '',
        ReferenceKind::EXPRESS_FORM => '/express_entities',
        ReferenceKind::EXPRESS_FORM_CONTROL => '/express_entities',
        ReferenceKind::FILE => '/files',
        ReferenceKind::FILE_FOLDER => '/file_folders',
        ReferenceKind::FILE_SET => '/file_sets',
        ReferenceKind::PAGE => '/pages',
        ReferenceKind::PAGE_FEED => '/page_feeds',
        ReferenceKind::PAGE_TYPE => '/page_types',
        ReferenceKind::SITE => '/sites',
        ReferenceKind::SOCIAL_LINK => '/sites/default',
        ReferenceKind::STACK => '/stacks',
        ReferenceKind::THUMBNAIL_TYPE => '/thumbnail_types',
        ReferenceKind::TOPIC => '/topic_trees',
        ReferenceKind::TOPIC_TREE => '/topic_trees',
        ReferenceKind::USER => '/users',
        ReferenceKind::USER_GROUP => '/groups',
    ];

    /**
     * @return string[] the kinds of reference, in the order a client is told about them
     */
    public function getList(): array
    {
        return array_keys(self::ENDPOINTS);
    }

    /**
     * @param string $reference one of the ReferenceKind::... constants
     * @return string an empty string where the things of the kind come inside another answer
     */
    public function getEndpoint(string $reference): string
    {
        return self::ENDPOINTS[$reference] ?? '';
    }

    /**
     * @param string $reference one of the ReferenceKind::... constants
     * @return string an empty string when the field holds no reference
     */
    public function describe(string $reference): string
    {
        switch ($reference) {
            case ReferenceKind::ATTRIBUTE_KEY:
                return 'An attribute key is named by its ID, and GET /attribute_categories/{categoryHandle}/keys lists the keys of a set.';
            case ReferenceKind::BOARD_INSTANCE:
                return 'An instance of a board is named by its ID, and GET /boards/{boardID}/instances lists the instances of a board.';
            case ReferenceKind::CALENDAR:
                return 'A calendar is named by its ID, and GET /calendars lists the calendars of this installation.';
            case ReferenceKind::CALENDAR_EVENT:
                return 'An event is named by its ID, and GET /calendars/{calendarID}/events lists the events of a calendar.';
            case ReferenceKind::CONTENT:
                return implode("\n", [
                    'HTML, where you can use:',
                    '- {CCM:BASE_URL} for the URL of the site',
                    '- {CCM:CID_<page ID>} for the URL of a page',
                    '- {CCM:FID_<file>} for the URL of a file',
                    '- {CCM:FID_DL_<file>} for the URL of a file, forcing its download',
                    '- <concrete-picture fID="<file>" /> for displaying an image',
                    self::FILE_FORMAT,
                ]);
            case ReferenceKind::EXPRESS_ENTITY:
                return 'An Express entity is named by its UUID, and ' . self::ENTITY_FORMAT;
            case ReferenceKind::EXPRESS_ENTRY:
                return 'Entries are named by UUID, or by ID when the UUID is missing, and the plural_handle of the entity says which path hands them over.';
            case ReferenceKind::EXPRESS_FORM:
                return 'A form of an Express entity is named by its UUID, and ' . self::ENTITY_FORMAT;
            case ReferenceKind::EXPRESS_FORM_CONTROL:
                return 'A control is named by the ID the field sets of its form carry, and ' . self::ENTITY_FORMAT;
            case ReferenceKind::FILE:
                return self::FILE_FORMAT;
            case ReferenceKind::FILE_FOLDER:
                return 'A folder of files is named by its ID.';
            case ReferenceKind::FILE_SET:
                return 'A set of files is named by its ID, and GET /file_sets lists the sets this request may use.';
            case ReferenceKind::PAGE:
                return 'A page is named by its ID.';
            case ReferenceKind::PAGE_FEED:
                return 'An RSS feed is named by its ID, and GET /page_feeds lists the feeds of this installation.';
            case ReferenceKind::PAGE_TYPE:
                return 'A page type is named by its ID.';
            case ReferenceKind::SITE:
                return 'A site is named by its ID, and GET /sites lists the sites this installation serves.';
            case ReferenceKind::SOCIAL_LINK:
                return 'A social link is named by its ID, as the social_links of a site answer give them.';
            case ReferenceKind::STACK:
                return 'A stack is named by its ID, and GET /stacks lists the stacks of the site.';
            case ReferenceKind::THUMBNAIL_TYPE:
                return 'A thumbnail type is named by its ID, and GET /thumbnail_types lists the types of this installation.';
            case ReferenceKind::TOPIC:
                return 'A topic is named by the ID of its node, and GET /topic_trees hands over the trees of this installation with their nodes.';
            case ReferenceKind::TOPIC_TREE:
                return 'A tree of topics is named by its ID, and GET /topic_trees lists the trees of this installation.';
            case ReferenceKind::USER:
                return 'A user is named by their ID, and GET /users lists the users of this installation.';
            case ReferenceKind::USER_GROUP:
                return 'A group of users is named by its ID, and GET /groups lists the groups of this installation.';
        }

        return '';
    }
}
