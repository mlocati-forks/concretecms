<?php

namespace Concrete\Core\Api\OpenApi;

class SpecModel implements \JsonSerializable
{

    /**
     * @var string
     */
    protected $objectName;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var SpecProperty[]
     */
    protected $properties = [];

    /**
     * What the schema holds, in words, for whoever reads the specification.
     *
     * @var string
     */
    protected $description;

    /**
     * SpecModel constructor.
     * @param string $objectName
     * @param string $name
     * @param string $description
     */
    public function __construct(string $objectName, string $name, string $description = '')
    {
        $this->objectName = $objectName;
        $this->name = $name;
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getObjectName(): string
    {
        return $this->objectName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function addProperty(SpecProperty $property)
    {
        $this->properties[] = $property;
        return $this;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = [];
        $data['title'] = $this->getName();
        if ($this->getDescription() !== '') {
            $data['description'] = $this->getDescription();
        }
        $data['properties'] = [];
        foreach ($this->properties as $property) {
            $data['properties'][$property->getPropertyKey()] = $property;
        }
        return $data;
    }


}
