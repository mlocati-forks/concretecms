<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Attribute\Category\ApiHandler;
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
        // the fields of the type win: a category adds to what the type says, it doesn't rewrite it
        return $this->describe($key)->jsonSerialize() + $this->getFieldsOfTheCategory($key);
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

    /**
     * Get what the category of the key adds to what its type says.
     *
     * @return array<string,mixed>
     */
    protected function getFieldsOfTheCategory(Key $key): array
    {
        $category = $key->getAttributeCategoryEntity();
        if ($category === null) {
            return [];
        }

        return ApiHandler::forCategory($category->getController())->getApiKeyFields($key);
    }
}
