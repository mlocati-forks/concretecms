<?php

namespace Concrete\Core\Api\OpenApi;

class SpecProperty implements \JsonSerializable
{

    /**
     * @var string
     */
    protected $propertyKey;

    /**
     * @var string
     */
    protected $title;

    /**
     * The types that a value may be of, or the schema of the specification that describes it: no
     * type at all says nothing about the values and lets anything go, while more than one travels
     * as an anyOf, since a schema of OpenAPI 3.0 names one type at a time.
     *
     * @var string[]|SpecPropertyRef
     */
    protected $type = [];

    /**
     * @var string|null
     */
    protected $format;

    /**
     * @var mixed
     */
    protected $items;

    /**
     * @var SpecProperty[]
     */
    protected $objectProperties;

    /**
     * @param string|string[]|SpecPropertyRef $type the type of a value, the types it may be of, or
     *                                              the schema that describes it
     */
    public function __construct(string $propertyKey, string $title, $type, $format = null, $items = null, $objectProperties = null)
    {
        $this->propertyKey = $propertyKey;
        $this->title = $title;
        $this->type = $type instanceof SpecPropertyRef ? $type : self::readTypes($type);
        $this->format = $format;
        $this->items = $items;
        $this->objectProperties = $objectProperties;

    }

    /**
     * Read the types a value may be of, where a type or a list of them is what was asked for.
     *
     * @param mixed $types
     *
     * @return string[]
     */
    private static function readTypes($types): array
    {
        $read = [];
        foreach ((array) $types as $type) {
            $type = (string) $type;
            if ($type !== '') {
                $read[] = $type;
            }
        }

        return $read;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|string[]|SpecPropertyRef the type of a value, the types it may be of where
     *                                         more than one does, or the schema describing it
     */
    public function getType()
    {
        if ($this->type instanceof SpecPropertyRef || count($this->type) !== 1) {
            return $this->type;
        }

        return $this->type[0];
    }

    /**
     * @return string
     */
    public function getPropertyKey(): string
    {
        return $this->propertyKey;
    }

    public function addObjectProperty(SpecProperty $property)
    {
        $this->objectProperties[$property->getPropertyKey()] = $property;
        return $this;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        if ($this->type instanceof SpecPropertyRef) {
            // a $ref is the whole schema of a value: OpenAPI 3.0 ignores whatever sits beside it
            return $this->type->jsonSerialize();
        }
        $data = [
            'property' => $this->getPropertyKey(),
            'title' => $this->getTitle(),
        ];
        if (count($this->type) === 1) {
            $data['type'] = $this->type[0];
        } elseif ($this->type !== []) {
            $data['anyOf'] = array_map(
                static function (string $type): array {
                    return ['type' => $type];
                },
                $this->type
            );
        }
        if (isset($this->format)) {
            $data['format'] = $this->format;
        }
        if ($this->items) {
            $data['items'] = $this->items;
        }
        if ($this->objectProperties) {
            $data['properties'] = $this->objectProperties;
        }
        return $data;
    }


}
