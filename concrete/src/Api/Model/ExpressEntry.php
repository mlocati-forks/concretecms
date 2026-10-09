<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="ExpressEntry",
 *     type="object",
 *     title="An entry of an Express entity this API does not serve on its own",
 *     description="The entries of such an entity come along with the ones they are associated with, and carry the fields every entry has"
 * )
 */
class ExpressEntry
{
    /**
     * @OA\Property(title="Entry public identifier")
     *
     * @var string
     */
    public $id;

    /**
     * @OA\Property(format="date", title="Date Added")
     *
     * @var string
     */
    public $date_added;

    /**
     * @OA\Property(format="date", title="Date Last Updated")
     *
     * @var string
     */
    public $date_last_updated;

    /**
     * @OA\Property(title="Label")
     *
     * @var string
     */
    public $label;

    /**
     * @OA\Property(title="URL")
     *
     * @var string
     */
    public $url;
}
