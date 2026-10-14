<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\FileSet as FileSetModel;
use Concrete\Core\Api\TokenScopes;
use Concrete\Core\File\Set\Set;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class FileSetTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Set $set): array
    {
        if (!$this->isVisible($set)) {
            return ['id' => (int) $set->getFileSetID()];
        }

        return $this->describe($set)->jsonSerialize();
    }

    /**
     * Get what a client reads of a set.
     */
    public function describe(Set $set): FileSetModel
    {
        $model = new FileSetModel();
        $model->id = (int) $set->getFileSetID();
        $model->name = (string) $set->getFileSetName();
        $type = (int) $set->getFileSetType();
        $model->public = $type === Set::TYPE_PUBLIC;
        // the files a user starred are kept in a set of their own, which is theirs like any other
        $model->starred = $type === Set::TYPE_STARRED;

        return $model;
    }

    /**
     * Whether the request may see the whole of a set of files: the scopes of its token settle
     * it first, and then the permissions, the ones the endpoint serving these things asks about.
     */
    protected function isVisible(Set $set): bool
    {
        return app(TokenScopes::class)->has('files:read');
    }
}
