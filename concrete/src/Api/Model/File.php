<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(title="File")
 */
class File
{

    /**
     * @OA\Property(title="File ID")
     */
    private $id;

    /**
     * @OA\Property(type="string", title="UUID")

     * @var string
     */
    private $uuid;

    /**
     * @OA\Property(type="string", title="Direct URL to access File")

     * @var string
     */
    private $url;

    /**
     * @OA\Property(type="string", title="Tracking URL", description="Forces download in all cases, tracks statistics")

     * @var string
     */
    private $tracked_url;

    /**
     * @OA\Property(type="string", title="File Type")

     * @var string
     */
    private $file_type;

    /**
     * @OA\Property(type="string", title="Extension")

     * @var string
     */
    private $extension;

    /**
     * @OA\Property(type="string", title="Title")

     * @var string
     */
    private $title;

    /**
     * @OA\Property(type="string", title="Description")

     * @var string
     */
    private $description;

    /**
     * @OA\Property(type="string", title="Tags")

     * @var string
     */
    private $tags;

    /**
     * @OA\Property(type="date", title="Date Added")

     * @var string
     */
    private $date_added;

    /**
     * @OA\Property(type="object", title="File Size")

     * @var string
     */
    private $size;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Sets the file is in, where the includes parameter asks for them",
     *     description="The ones this request may use, so a set somebody else keeps for themselves is left out",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/FileSet")
     *     )
     * )
     *
     * @var array
     */
    private $sets;

    /**
     * @OA\Property(type="integer", format="int64", title="Folder ID")
     *
     * @var int
     */
    private $folder;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Custom attributes of the file, where the includes parameter asks for them",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/CustomAttribute")
     *     )
     * )
     *
     * @var array
     */
    private $custom_attributes;

}