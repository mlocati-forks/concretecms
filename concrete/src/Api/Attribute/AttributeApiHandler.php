<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Attribute;

use Concrete\Core\Api\ApiResourceValueInterface;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Api\OpenApi\SpecProperty;
use Concrete\Core\Attribute\AttributeInterface;
use Concrete\Core\Attribute\Controller;
use Concrete\Core\Attribute\DefaultController;
use Concrete\Core\Entity\Attribute\Key\Key;
use League\Fractal\Manager;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * What the API does with the keys and the values of an attribute type: a type that needs more than
 * the defaults comes with a Concrete\Attribute\<Handle>\Api class beside its controller, extending
 * this one.
 *
 * The defaults are what the API did before the handlers existed, the deprecated interfaces of a
 * controller included.
 *
 * @see \Concrete\Core\Attribute\Controller::getApiHandler()
 */
class AttributeApiHandler
{
    /**
     * @var \Concrete\Core\Attribute\AttributeInterface
     */
    protected $controller;

    public function __construct(AttributeInterface $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get the handler of the type a controller drives, whatever that controller extends.
     */
    public static function forController(AttributeInterface $controller): self
    {
        return $controller instanceof Controller ? $controller->getApiHandler() : new self($controller);
    }

    /**
     * Describe a key of this type to the clients of the API, which read it to know what a value of
     * the key may be. A type whose keys carry more than the others answers with a model of its own.
     */
    public function describeApiKey(Key $key): AttributeKeyModel
    {
        $model = new AttributeKeyModel();
        $this->fillApiKey($model, $key);

        return $model;
    }

    /**
     * Fill the fields that the key of every type has.
     */
    protected function fillApiKey(AttributeKeyModel $model, Key $key): void
    {
        $type = $key->getAttributeType();
        $model->id = (int) $key->getAttributeKeyID();
        $model->handle = (string) $key->getAttributeKeyHandle();
        $model->name = (string) $key->getAttributeKeyDisplayName('text');
        $model->type = $type === null ? '' : (string) $type->getAttributeTypeHandle();
    }

    /**
     * Get what a value of a key of this type looks like in the specification of the API.
     */
    public function getApiSpecProperty(Key $key): SpecProperty
    {
        if ($this->controller instanceof OpenApiSpecifiableInterface) {
            return $this->controller->getOpenApiSpecProperty($key);
        }

        return new SpecProperty(
            (string) $key->getAttributeKeyHandle(),
            (string) $key->getAttributeKeyDisplayName(),
            // a type driven by the default controller keeps text; of any other one we know nothing
            $this->controller instanceof DefaultController ? 'string' : SpecProperty::TYPE_ANY
        );
    }

    /**
     * Get the value the controller holds, as the API hands it to its clients.
     *
     * @return mixed NULL where the attribute has no value to hand over
     */
    public function getApiValue()
    {
        if ($this->controller instanceof ApiResourceValueInterface) {
            $resource = $this->controller->getApiValueResource();

            return $resource === null ? null : app(Manager::class)->createData($resource)->toArray();
        }
        if ($this->controller instanceof SimpleApiAttributeValueInterface) {
            return $this->controller->getApiAttributeValue();
        }

        return $this->controller->getSearchIndexValue();
    }

    /**
     * Turn a value received by the API into the value of an attribute.
     *
     * @param mixed $json a scalar, or the array of a more complex request body object
     *
     * @return mixed the value to save, falsy where the request names none
     */
    public function createApiValue($json)
    {
        if ($this->controller instanceof SupportsAttributeValueFromJsonInterface) {
            return $this->controller->createAttributeValueFromNormalizedJson($json);
        }
        if ($this->controller instanceof DefaultController || is_scalar($json)) {
            return $this->controller->createAttributeValue((string) $json);
        }

        // nothing is known about the values of this type, and a cast would hand it the word "Array"
        return $this->controller->createAttributeValue($json);
    }
}
