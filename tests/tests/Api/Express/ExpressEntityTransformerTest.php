<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Express;

use Concrete\Core\Api\Fractal\Transformer\ExpressEntityTransformer;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\Form;
use Concrete\Core\Entity\Express\ManyToOneAssociation;
use Concrete\Core\Entity\Express\OneToManyAssociation;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A block displaying the entries of an Express entity names the entity, one of its forms and its
 * associations by their IDs: this is what a client reads to know which ones there are.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\ExpressEntityTransformer
 */
class ExpressEntityTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAnEntitySaysWhatABlockDisplayingItsEntriesNeeds(): void
    {
        $transformed = $this->transform($this->createEntity([
            'getId' => '1cafebab-babe-cafe-babe-1cafebabe1ca',
            'getHandle' => 'registration',
            'getPluralHandle' => 'registrations',
            'getName' => 'Registration',
            'getDescription' => 'Who signed up',
        ]));

        static::assertSame('1cafebab-babe-cafe-babe-1cafebabe1ca', $transformed['id']);
        static::assertSame('registration', $transformed['handle']);
        static::assertSame('registrations', $transformed['plural_handle']);
        static::assertSame('Registration', $transformed['name']);
        static::assertSame('Who signed up', $transformed['description']);
    }

    public function testTheFormsOfTheEntityComeWithIt(): void
    {
        $entity = $this->createEntity([], [], [
            $this->createForm('2cafebab-babe-cafe-babe-2cafebabe2ca', 'Sign up'),
        ]);

        static::assertAnswerIs(
            [['id' => '2cafebab-babe-cafe-babe-2cafebabe2ca', 'name' => 'Sign up']],
            $this->transform($entity)['forms']
        );
    }

    /**
     * An entry is written with the name of the property, while a list of entries is searched by the
     * ID of the association.
     */
    public function testAnAssociationSaysHowManyEntriesItHoldsAndWhereTheyBelong(): void
    {
        $entity = $this->createEntity([], [
            $this->createAssociation(OneToManyAssociation::class, '4cafebab-babe-cafe-babe-4cafebabe4ca', 'attendees', 'person'),
            $this->createAssociation(ManyToOneAssociation::class, '5cafebab-babe-cafe-babe-5cafebabe5ca', 'venue', 'place'),
        ]);

        static::assertAnswerIs([
            [
                'id' => '4cafebab-babe-cafe-babe-4cafebabe4ca',
                'property' => 'attendees',
                'type' => 'many',
                'target_entity_id' => 'person-id',
                'target_entity_handle' => 'person',
            ],
            [
                'id' => '5cafebab-babe-cafe-babe-5cafebabe5ca',
                'property' => 'venue',
                'type' => 'one',
                'target_entity_id' => 'place-id',
                'target_entity_handle' => 'place',
            ],
        ], $this->transform($entity)['associations']);
    }

    public function testTheFieldsAreTheOnesTheSpecificationDescribes(): void
    {
        $this->assertFieldsAre('ExpressEntity', $this->transform($this->createEntity([])));
    }

    public function testTheFieldsOfTheThingsTheEntityHoldsAreDescribedToo(): void
    {
        $transformed = $this->transform($this->createEntity([], [
            $this->createAssociation(OneToManyAssociation::class, '4caf', 'attendees', 'person'),
        ], [
            $this->createForm('2caf', 'Sign up'),
        ]));

        $this->assertFieldsAre('ExpressEntityAssociation', $transformed['associations'][0]);
        $this->assertFieldsAre('ExpressEntityForm', $transformed['forms'][0]);
        $this->assertFieldsAre('ExpressEntityColumn', $transformed['columns'][0]);
        $this->assertFieldsAre('ExpressEntityFilter', $transformed['filters'][0]);
    }

    /**
     * @return array<string,mixed> what a client reads of the entity
     */
    private function transform(Entity $entity): array
    {
        // the columns and the filters come from the search machinery of the core, which asks the database
        $transformer = new class extends ExpressEntityTransformer {
            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\ExpressEntityTransformer::getColumns()
             */
            protected function getColumns(Entity $entity): array
            {
                return [['key' => 'ak_first_name', 'name' => 'First Name', 'sortable' => true]];
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\ExpressEntityTransformer::getFilters()
             */
            protected function getFilters(Entity $entity): array
            {
                return [['key' => 'keywords', 'name' => 'Keywords', 'group' => 'Core Properties']];
            }
        };

        return $transformer->transform($entity);
    }

    /**
     * @param array<string,mixed> $answers what the entity answers, by the name of the method asking
     * @param \Concrete\Core\Entity\Express\Association[] $associations
     * @param \Concrete\Core\Entity\Express\Form[] $forms
     */
    private function createEntity(array $answers, array $associations = [], array $forms = []): Entity
    {
        $answers += [
            'getId' => '1cafebab-babe-cafe-babe-1cafebabe1ca',
            'getHandle' => 'registration',
            'getPluralHandle' => 'registrations',
            'getName' => 'Registration',
            'getDescription' => '',
            'getAssociations' => new ArrayCollection($associations),
            'getForms' => new ArrayCollection($forms),
        ];
        $entity = $this->createMock(Entity::class);
        foreach ($answers as $method => $answer) {
            $entity->method($method)->willReturn($answer);
        }

        return $entity;
    }

    /**
     * @param string $class one of the Association classes
     *
     * @return \Concrete\Core\Entity\Express\Association
     */
    private function createAssociation(string $class, string $id, string $propertyName, string $targetHandle)
    {
        $target = $this->createMock(Entity::class);
        $target->method('getId')->willReturn($targetHandle . '-id');
        $target->method('getHandle')->willReturn($targetHandle);
        $association = $this->createMock($class);
        $association->method('getId')->willReturn($id);
        $association->method('getTargetPropertyName')->willReturn($propertyName);
        $association->method('getTargetEntity')->willReturn($target);

        return $association;
    }

    /**
     * @return \Concrete\Core\Entity\Express\Form
     */
    private function createForm(string $id, string $name)
    {
        $form = $this->createMock(Form::class);
        $form->method('getId')->willReturn($id);
        $form->method('getName')->willReturn($name);

        return $form;
    }
}
