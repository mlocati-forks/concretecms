<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\FileFolder;

use Concrete\Core\Api\Model\FileFolder;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="FileFolderDetail",
 *     type="object",
 *     title="A folder of files asked for by itself",
 *     allOf={@OA\Schema(ref="#/components/schemas/FileFolder")}
 * )
 */
class Detail extends FileFolder
{
    /**
     * @OA\Property(type="integer", format="int64", nullable=true, title="ID of the folder holding this one, NULL for the root of the file manager")
     *
     * @var int|null
     */
    private $parent_id;

    /**
     * @OA\Property(type="array", title="Folders this one holds directly", description="Going further down takes a request per folder", @OA\Items(ref="#/components/schemas/FileFolderSummary"))
     *
     * @var \Concrete\Core\Api\Model\FileFolder\Summary[]
     */
    private $sub_folders;
}
