<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(title="PageFeed model", description="An RSS feed this installation publishes pages through.")
 */
class PageFeed implements \JsonSerializable
{
    /**
     * @OA\Property(format="int64", title="ID", description="The one that the page_list blocks publishing through this feed name")
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(title="Handle of the feed, which its address is built with")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Title of the feed")
     *
     * @var string
     */
    public $title;

    /**
     * @OA\Property(title="Description of the feed")
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(title="Address the feed is served at")
     *
     * @var string
     */
    public $url;

    /**
     * @OA\Property(format="int64", title="ID of the page whose children the feed publishes", description="NULL where the feed publishes the pages of the whole site")
     *
     * @var int|null
     */
    public $parent_page_id;

    /**
     * @OA\Property(title="Whether the feed publishes the pages below the children too")
     *
     * @var bool
     */
    public $include_all_descendants;

    /**
     * @OA\Property(title="Handle of the page type the feed is limited to, empty where it publishes every type")
     *
     * @var string
     */
    public $page_type;

    /**
     * @OA\Property(title="Whether the feed publishes the pages marked as featured alone")
     *
     * @var bool
     */
    public $only_featured;

    /**
     * @OA\Property(title="Whether the feed publishes the aliases of a page as well")
     *
     * @var bool
     */
    public $include_aliases;

    /**
     * @OA\Property(title="Whether the feed publishes the pages the site keeps for itself, as the login one")
     *
     * @var bool
     */
    public $include_system_pages;

    /**
     * @OA\Property(enum={"description", "area"}, title="What every item of the feed carries", description="The description of the page, or the blocks of one of its areas")
     *
     * @var string
     */
    public $content;

    /**
     * @OA\Property(title="Handle of the area the items carry, empty where they carry the description")
     *
     * @var string
     */
    public $area;

    /**
     * {@inheritdoc}
     *
     * @see \JsonSerializable::jsonSerialize()
     *
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}
