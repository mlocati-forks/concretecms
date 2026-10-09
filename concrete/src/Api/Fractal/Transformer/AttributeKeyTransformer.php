<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Entity\Attribute\Key\Key;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class AttributeKeyTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Key $key): array
    {
        return $this->describe($key)->jsonSerialize();
    }

    protected function describe(Key $key): AttributeKeyModel
    {
        if ($key->getAttributeType() !== null) {
            return AttributeApiHandler::forController($key->getController())->describeApiKey($key);
        }
        // a key whose type left with the package that brought it has no controller to ask
        $model = new AttributeKeyModel();
        $model->id = (int) $key->getAttributeKeyID();
        $model->handle = (string) $key->getAttributeKeyHandle();
        $model->name = (string) $key->getAttributeKeyDisplayName('text');
        $model->type = '';

        return $model;
    }

}
