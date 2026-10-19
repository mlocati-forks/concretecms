<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="NewFile",
 *     description="Uploading a file: this request body carries the file itself, while the other ways of adding one are asked for with a JSON request body"
 * )
 */
class NewFile
{

    /**
     * @OA\Property(type="integer", format="int64", title="ID of the file folder the file is added to", description="GET /file_folders lists the folders of this site: left out, the file goes to the root folder of the file manager.")
     *
     * @var int
     */
    private $folder;

    /**
     * @OA\Property(type="string", format="binary", title="File to upload", description="The contents of the file, sent as the ""file"" part of the multipart/form-data request body: that's what a <input type=""file"" name=""file""> field of an HTML form sends. The file keeps the name it is uploaded with.")
     *
     * @var string
     */
    private $file;

}
