<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\ExpressEntity as ExpressEntityModel;
use Concrete\Core\Api\Model\ExpressEntity\Association as AssociationModel;
use Concrete\Core\Api\Model\ExpressEntity\Column as ColumnModel;
use Concrete\Core\Api\Model\ExpressEntity\Filter as FilterModel;
use Concrete\Core\Api\Model\ExpressEntity\Form as FormModel;
use Concrete\Core\Api\Model\ExpressEntity\Form\Control as ControlModel;
use Concrete\Core\Api\Model\ExpressEntity\Form\FieldSet as FieldSetModel;
use Concrete\Core\Entity\Express\Association;
use Concrete\Core\Entity\Express\Control\AssociationControl;
use Concrete\Core\Entity\Express\Control\AttributeKeyControl;
use Concrete\Core\Entity\Express\Control\Control;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\FieldSet;
use Concrete\Core\Entity\Express\Form;
use Concrete\Core\Entity\Express\ManyToManyAssociation;
use Concrete\Core\Entity\Express\OneToManyAssociation;
use Concrete\Core\Express\Search\SearchProvider;
use Concrete\Core\Search\Column\Column;
use Concrete\Core\Search\Field\FieldInterface;
use Concrete\Core\Search\Field\GroupInterface;
use Concrete\Core\Search\Field\ManagerFactory;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class ExpressEntityTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Entity $entity): array
    {
        $model = new ExpressEntityModel();
        $model->id = (string) $entity->getId();
        $model->handle = (string) $entity->getHandle();
        $model->plural_handle = (string) $entity->getPluralHandle();
        $model->name = (string) $entity->getName();
        $model->description = (string) $entity->getDescription();
        $model->columns = $this->describeColumns($entity);
        $model->filters = $this->describeFilters($entity);
        $model->associations = $this->describeAssociations($entity);
        $model->forms = $this->describeForms($entity);

        return $model->jsonSerialize();
    }

    /**
     * @return \Concrete\Core\Api\Model\ExpressEntity\Column[]
     */
    protected function describeColumns(Entity $entity): array
    {
        $provider = app(SearchProvider::class, ['entity' => $entity, 'category' => $entity->getAttributeKeyCategory()]);
        $columns = [];
        foreach ($provider->getAvailableColumnSet()->getColumns() as $column) {
            if (!$column instanceof Column) {
                continue;
            }
            $model = new ColumnModel();
            $model->key = (string) $column->getColumnKey();
            $model->name = (string) $column->getColumnName();
            $model->sortable = (bool) $column->isColumnSortable();
            $columns[] = $model;
        }

        return $columns;
    }

    /**
     * @return \Concrete\Core\Api\Model\ExpressEntity\Filter[]
     */
    protected function describeFilters(Entity $entity): array
    {
        $manager = ManagerFactory::get('express');
        $manager->setExpressCategory($entity->getAttributeKeyCategory());
        $filters = [];
        foreach ($manager->getGroups() as $group) {
            if (!$group instanceof GroupInterface) {
                continue;
            }
            foreach ($group->getFields() as $field) {
                if (!$field instanceof FieldInterface) {
                    continue;
                }
                $model = new FilterModel();
                $model->key = (string) $field->getKey();
                $model->name = (string) $field->getDisplayName();
                $model->group = (string) $group->getName();
                $filters[] = $model;
            }
        }

        return $filters;
    }

    /**
     * @return \Concrete\Core\Api\Model\ExpressEntity\Association[]
     */
    protected function describeAssociations(Entity $entity): array
    {
        $associations = [];
        foreach ($entity->getAssociations() as $association) {
            if (!$association instanceof Association) {
                continue;
            }
            $target = $association->getTargetEntity();
            if (!$target instanceof Entity) {
                continue;
            }
            $toMany = $association instanceof OneToManyAssociation || $association instanceof ManyToManyAssociation;
            $model = new AssociationModel();
            $model->id = (string) $association->getId();
            $model->property = (string) $association->getTargetPropertyName();
            $model->type = $toMany ? 'many' : 'one';
            $model->target_entity_id = (string) $target->getId();
            $model->target_entity_handle = (string) $target->getHandle();
            $associations[] = $model;
        }

        return $associations;
    }

    /**
     * @return \Concrete\Core\Api\Model\ExpressEntity\Form[]
     */
    protected function describeForms(Entity $entity): array
    {
        $forms = [];
        foreach ($entity->getForms() as $form) {
            if (!$form instanceof Form) {
                continue;
            }
            $model = new FormModel();
            $model->id = (string) $form->getId();
            $model->name = (string) $form->getName();
            $model->field_sets = $this->describeFieldSets($form);
            $forms[] = $model;
        }

        return $forms;
    }

    /**
     * @return \Concrete\Core\Api\Model\ExpressEntity\Form\FieldSet[]
     */
    protected function describeFieldSets(Form $form): array
    {
        $sets = [];
        foreach ($form->getFieldSets() as $set) {
            if (!$set instanceof FieldSet) {
                continue;
            }
            $model = new FieldSetModel();
            $model->title = (string) $set->getTitle();
            $model->description = (string) $set->getDescription();
            $model->controls = $this->describeControls($set);
            $sets[] = $model;
        }

        return $sets;
    }

    /**
     * @return \Concrete\Core\Api\Model\ExpressEntity\Form\Control[]
     */
    protected function describeControls(FieldSet $set): array
    {
        $controls = [];
        foreach ($set->getControls() as $control) {
            if (!$control instanceof Control) {
                continue;
            }
            $model = new ControlModel();
            $model->id = (string) $control->getId();
            $model->type = (string) $control->getType();
            $model->label = (string) $control->getDisplayLabel();
            $model->required = (bool) $control->isRequired();
            $key = $control instanceof AttributeKeyControl ? $control->getAttributeKey() : null;
            $model->attribute_key = $key === null ? '' : (string) $key->getAttributeKeyHandle();
            $association = $control instanceof AssociationControl ? $control->getAssociation() : null;
            $model->association = $association === null ? '' : (string) $association->getTargetPropertyName();
            $controls[] = $model;
        }

        return $controls;
    }
}
