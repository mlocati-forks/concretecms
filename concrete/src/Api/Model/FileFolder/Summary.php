<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\FileFolder;

use Concrete\Core\Api\Model\FileFolder;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="FileFolderSummary",
 *     type="object",
 *     title="A folder of files listed, or named in the answer about another one",
 *     allOf={@OA\Schema(ref="#/components/schemas/FileFolder")}
 * )
 */
class Summary extends FileFolder
{
    /**
     * @OA\Property(type="boolean", title="Whether other folders are filed in this one", description="Says what the folder holds, not what the request may view of it: asking for this folder by itself hands those folders over")
     *
     * @var bool
     */
    private $has_sub_folders;
}
