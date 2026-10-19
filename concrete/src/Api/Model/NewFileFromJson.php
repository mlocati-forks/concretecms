<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="NewFileFromJson",
 *     description="Adding a file without sending a multipart/form-data request body: specify one of url, incoming_filename, and contents together with filename, and only one of them"
 * )
 */
class NewFileFromJson
{
    /**
     * @OA\Property(type="integer", format="int64", title="ID of the file folder the file is added to", description="GET /file_folders lists the folders of this site: left out, the file goes to the root folder of the file manager.")
     *
     * @var int
     */
    private $folder;

    /**
     * @OA\Property(format="uri", title="URL of the file to be downloaded", description="Let Concrete download the file itself. Unless filename is specified, the name of the file is taken from the URL (or from the mime type declared by the remote server); in both the cases the extension must be one that this installation accepts.")
     *
     * @var string
     */
    private $url;

    /**
     * @OA\Property(title="Name of the file", description="The name the file will have, for example image.png: its extension must be one that this installation accepts. It's required when sending the contents, and optional when downloading from an url (where it overrides the name taken from the URL) or when taking a file of the incoming directory (where it overrides the name the file has there). It does not hold for an uploaded file, which keeps the name it is sent with: a request that sends both is refused.")
     *
     * @var string
     */
    private $filename;

    /**
     * @OA\Property(format="byte", title="Contents of the file", description="The contents of the file, base64-encoded, to be sent together with filename. Use a multipart/form-data request body if you can: it doesn't need the contents to be encoded.")
     *
     * @var string
     */
    private $contents;

    /**
     * @OA\Property(title="Name of a file sitting in the incoming directory", description="Take a file the administrators of this site have put in the incoming directory of its default storage location, instead of sending its contents: this is a name and not a path, and the file keeps that name unless filename says another one. This API does not say what is in that directory, so the name comes from whoever put the file there.")
     *
     * @var string
     */
    private $incoming_filename;

    /**
     * @OA\Property(title="Whether the file is taken away from the incoming directory", description="It holds for incoming_filename alone: left out, the file stays there and can be added again.")
     *
     * @var bool
     */
    private $remove_from_incoming;
}
