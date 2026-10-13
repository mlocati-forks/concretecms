<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\ReferenceKind as ReferenceKindModel;
use Concrete\Core\Api\ReferenceCatalog;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class ReferenceKindTransformer extends TransformerAbstract
{
    /**
     * @param string $reference one of the \Concrete\Core\Reference\ReferenceKind::... constants
     *
     * @return array<string,mixed>
     */
    public function transform(string $reference): array
    {
        return $this->describe($reference)->jsonSerialize();
    }

    /**
     * Get what a client reads of a kind of reference.
     *
     * @param string $reference one of the \Concrete\Core\Reference\ReferenceKind::... constants
     */
    public function describe(string $reference): ReferenceKindModel
    {
        $catalog = app(ReferenceCatalog::class);
        $model = new ReferenceKindModel();
        $model->handle = $reference;
        $model->description = $catalog->describe($reference);
        $model->endpoint = $catalog->getEndpoint($reference);

        return $model;
    }
}
