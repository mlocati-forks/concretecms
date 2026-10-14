<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Carbon\Carbon;
use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Core\Api\File\SetAccess;
use Concrete\Core\Api\Resources;
use Concrete\Core\Permission\Checker;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class FileTransformer extends TransformerAbstract
{

    protected $availableIncludes = [
        'custom_attributes',
        'sets',
    ];

    /**
     * Basic transforming of a file entity into an array
     *
     * @param FileEntity $file
     * @return array
     */
    public function transform(FileEntity $file)
    {
        if (!$this->isVisible($file)) {
            return ['id' => $file->hasFileUUID() ? $file->getFileUUID() : $file->getFileID()];
        }

        $version = $file->getApprovedVersion();
        $folderID = null;
        if ($fileFolder = $file->getFileFolderObject()) {
            $folderID = $fileFolder->getTreeNodeID();
        }
        $data['id'] = $file->hasFileUUID() ? $file->getFileUUID() : $file->getFileID();
        $data['title'] = $version->getTitle();
        $data['description'] = $version->getDescription();
        $data['tags'] = $version->getTags();
        $data['url'] = $version->getURL();
        $data['file_type'] = $version->getType();
        $data['extension'] = $version->getExtension();
        $data['tracked_url'] = (string) $version->getDownloadURL();
        $data['folder'] = $folderID;
        $data['date_added'] = Carbon::make($version->getDateAdded())->toAtomString();
        $data['size'] =
            [
                'description' => $version->getSize(),
                'exact' => $version->getFullSize(),
            ];
        return $data;
    }

    public function includeCustomAttributes(FileEntity $file)
    {
        $values = $file->getObjectAttributeCategory()->getAttributeValues($file);
        return new Collection($values, new AttributeValueTransformer(), Resources::RESOURCE_CUSTOM_ATTRIBUTES);
    }

    /**
     * @return \League\Fractal\Resource\Collection
     */
    public function includeSets(FileEntity $file)
    {
        $sets = app(SetAccess::class)->getSetsOf($file);

        return new Collection($sets, new FileSetTransformer(), Resources::RESOURCE_FILE_SETS);
    }

    /**
     * Whether the request may see the whole of a file: the scopes of its token settle
     * it first, and then the permissions, the ones the endpoint serving these things asks about.
     */
    protected function isVisible(FileEntity $file): bool
    {
        return app(TokenScopes::class)->has('files:read')
            && (bool) (new Checker($file))->canViewFileInFileManager();
    }
}
