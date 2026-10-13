<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\FileSet as FileSetModel;
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
}
