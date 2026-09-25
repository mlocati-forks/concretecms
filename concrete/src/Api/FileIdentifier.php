<?php

declare(strict_types=1);

namespace Concrete\Core\Api;

use Concrete\Core\File\File;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A file travels by its UUID, or by its ID when it has none: the UUID keeps the IDs of the site to itself.
 */
class FileIdentifier
{
    /**
     * @param int|numeric-string|null $fileID the ID of the file, as a column of a block holds it
     * @return int|string|null the UUID of the file, or its ID when the file has no UUID
     */
    public function forApi($fileID)
    {
        if (!is_numeric($fileID) || (int) $fileID <= 0) {
            return $fileID;
        }
        $file = File::getByID((int) $fileID);
        $uuid = $file === null ? '' : (string) $file->getFileUUID();

        return $uuid === '' ? (int) $fileID : $uuid;
    }

    /**
     * @param mixed $identifier the UUID of a file, or its ID when the file has no UUID
     * @return int 0 when no file of the site answers to the identifier
     */
    public function fromApi($identifier): int
    {
        $file = File::getByUUIDOrID($identifier);

        return $file === null ? 0 : (int) $file->getFileID();
    }

    /**
     * Turn the files that a rich text refers to into the identifiers that the API hands to its clients.
     *
     * The stored forms take a UUID as well as an ID, so what comes back from a client needs no
     * translation: this is a one-way street.
     */
    public function contentForApi(string $content): string
    {
        if ($content === '') {
            return $content;
        }
        $patterns = [
            // a file that the editor links to, and one that it links to for downloading
            '/\{CCM:FID_(DL_)?(\d+)\}/i',
            // a file that the editor displays inline
            '/(<concrete-picture\s[^>]*?fID\s*=\s*["\'])(\d+)(["\'])/i',
        ];

        return (string) preg_replace_callback(
            $patterns,
            function (array $matches): string {
                if (count($matches) === 4) {
                    // the attribute of a picture
                    return $matches[1] . $this->forApi($matches[2]) . $matches[3];
                }

                return '{CCM:FID_' . $matches[1] . $this->forApi($matches[2]) . '}';
            },
            $content
        );
    }
}
