<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="NewFileMeta",
 *     type="object",
 *     title="What adding a file answers beside the file itself"
 * )
 */
class NewFileMeta
{
    /**
     * @OA\Property(type="boolean", title="Whether the incoming directory holds that file no more", description="It is answered only where the request asked for the file to be taken away with remove_from_incoming. FALSE says the file was added to the file manager all the same, and that the copy sitting in the incoming directory could not be deleted: adding it again would add it twice.")
     *
     * @var bool
     */
    private $removed_from_incoming;
}
