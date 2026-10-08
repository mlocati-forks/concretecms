<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Express;

use Concrete\Core\Api\Fractal\Transformer\ExpressEntityTransformer;
use Concrete\Core\Api\Model\ExpressEntity\Column as ColumnModel;
use Concrete\Core\Api\Model\ExpressEntity\Filter as FilterModel;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Express\Control\AttributeKeyControl;
use Concrete\Core\Entity\Express\Control\TextControl;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\FieldSet;
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
            [['id' => '2cafebab-babe-cafe-babe-2cafebabe2ca', 'name' => 'Sign up', 'field_sets' => []]],
            $this->transform($entity)['forms']
        );
    }

    /**
     * An express_form block names a control of the form, the reply-to address among them, so a client
     * reads which ones the form is made of and what each of them asks for, grouped the way the form
     * shows them.
     */
    public function testTheControlsOfAFormComeWithItGroupedAsItShowsThem(): void
    {
        $entity = $this->createEntity([], [], [
            $this->createForm('2caf', 'Sign up', [
                $this->createFieldSet('Basics', 'Who you are', [
                    $this->createTextControl('3caf', 'Fill this in'),
                    $this->createAttributeControl('4caf', 'Email', 'email', true),
                ]),
                $this->createFieldSet('', '', [$this->createAttributeControl('5caf', 'Notes', 'notes')]),
            ]),
        ]);

        static::assertAnswerIs([
            [
                'title' => 'Basics',
                'description' => 'Who you are',
                'controls' => [
                    [
                        'id' => '3caf',
                        'type' => 'entity_property',
                        'label' => 'Fill this in',
                        'required' => false,
                        'attribute_key' => '',
                        'association' => '',
                    ],
                    [
                        'id' => '4caf',
                        'type' => 'attribute_key',
                        'label' => 'Email',
                        'required' => true,
                        'attribute_key' => 'email',
                        'association' => '',
                    ],
                ],
            ],
            [
                'title' => '',
                'description' => '',
                'controls' => [
                    [
                        'id' => '5caf',
                        'type' => 'attribute_key',
                        'label' => 'Notes',
                        'required' => false,
                        'attribute_key' => 'notes',
                        'association' => '',
                    ],
                ],
            ],
        ], $this->transform($entity)['forms'][0]['field_sets']);
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
            $this->createForm('2caf', 'Sign up', [$this->createFieldSet('Basics', '', [$this->createTextControl('3caf', 'Fill this in')])]),
        ]));

        $this->assertFieldsAre('ExpressEntityAssociation', $transformed['associations'][0]);
        $this->assertFieldsAre('ExpressEntityForm', $transformed['forms'][0]);
        $this->assertFieldsAre('ExpressEntityFormFieldSet', $transformed['forms'][0]['field_sets'][0]);
        $this->assertFieldsAre('ExpressEntityFormControl', $transformed['forms'][0]['field_sets'][0]['controls'][0]);
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
             * @see \Concrete\Core\Api\Fractal\Transformer\ExpressEntityTransformer::describeColumns()
             */
            protected function describeColumns(Entity $entity): array
            {
                $column = new ColumnModel();
                $column->key = 'ak_first_name';
                $column->name = 'First Name';
                $column->sortable = true;

                return [$column];
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\ExpressEntityTransformer::describeFilters()
             */
            protected function describeFilters(Entity $entity): array
            {
                $filter = new FilterModel();
                $filter->key = 'keywords';
                $filter->name = 'Keywords';
                $filter->group = 'Core Properties';

                return [$filter];
            }
        };

        // what a client reads, which is this answer once the serializer has had it
        return json_decode((string) json_encode($transformer->transform($entity)), true);
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
    /**
     * @param object[] $fieldSets the groups of fields the form is made of, in the order it shows them
     */
    private function createForm(string $id, string $name, array $fieldSets = [])
    {
        $form = $this->createMock(Form::class);
        $form->method('getId')->willReturn($id);
        $form->method('getName')->willReturn($name);
        $form->method('getFieldSets')->willReturn($fieldSets);

        return $form;
    }

    /**
     * @param object[] $controls the controls of the group, in the order the form shows them
     */
    private function createFieldSet(string $title, string $description, array $controls)
    {
        $set = $this->createMock(FieldSet::class);
        $set->method('getTitle')->willReturn($title);
        $set->method('getDescription')->willReturn($description);
        $set->method('getControls')->willReturn($controls);

        return $set;
    }

    private function createAttributeControl(string $id, string $label, string $keyHandle, bool $required = false)
    {
        $key = $this->createMock(Key::class);
        $key->method('getAttributeKeyHandle')->willReturn($keyHandle);
        $control = $this->createMock(AttributeKeyControl::class);
        $control->method('getId')->willReturn($id);
        $control->method('getType')->willReturn('attribute_key');
        $control->method('getDisplayLabel')->willReturn($label);
        $control->method('isRequired')->willReturn($required);
        $control->method('getAttributeKey')->willReturn($key);

        return $control;
    }

    private function createTextControl(string $id, string $label)
    {
        $control = $this->createMock(TextControl::class);
        $control->method('getId')->willReturn($id);
        $control->method('getType')->willReturn('entity_property');
        $control->method('getDisplayLabel')->willReturn($label);
        $control->method('isRequired')->willReturn(false);

        return $control;
    }
}
