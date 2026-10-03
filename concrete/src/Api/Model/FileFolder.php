<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="FileFolder",
 *     type="object",
 *     title="What every answer about a folder of files says",
 * )
 */
abstract class FileFolder
{
    /**
     * @OA\Property(type="integer", format="int64", title="File Folder ID", description="What a file is moved to, the root of the file manager included")
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(type="string", title="File Folder Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Names of the folders down to this one, separated by slashes")
     *
     * @var string
     */
    private $path;

    /**
     * @OA\Property(type="boolean", title="Whether files are filed in this folder itself", description="Says what the folder holds, not what the request may read of it: the file endpoints answer for the files themselves")
     *
     * @var bool
     */
    private $has_files;
}
