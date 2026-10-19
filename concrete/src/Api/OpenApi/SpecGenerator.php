<?php

namespace Concrete\Core\Api\OpenApi;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Events\GenerateApiSpecEvent;
use Concrete\Core\Api\OpenApi\Factory\ExpressEntitySpecFactory;
use Concrete\Core\Api\ReferenceCatalog;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Concrete\Core\Attribute\Category\FileCategory;
use Concrete\Core\Attribute\Category\PageCategory;
use Concrete\Core\Attribute\Category\UserCategory;
use Concrete\Core\Block\Events\BlockDelete;
use Concrete\Core\Events\EventDispatcher;
use Concrete\Core\Express\ObjectManager;
use Concrete\Core\Reference\ReferenceKind;
use Concrete\Core\StyleCustomizer\Inline\StyleValues;
use OpenApi\Annotations\Components;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;

class SpecGenerator implements ApplicationAwareInterface
{

    use ApplicationAwareTrait;

    /**
     * @var SpecMerger
     */
    protected $merger;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var SourceRegistry
     */
    protected $sourceRegistry;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher, ObjectManager $objectManager, SpecMerger $merger, SourceRegistry $sourceRegistry)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->objectManager = $objectManager;
        $this->merger = $merger;
        $this->sourceRegistry = $sourceRegistry;
    }

    private function addExpressSpec(OpenApi $openApi)
    {
        $objects = $this->objectManager->getEntities(true)->findBy(['include_in_rest_api' => true, 'is_published' => true]);
        foreach ($objects as $object) {
            $factory = new ExpressEntitySpecFactory();
            $spec = $factory->build($object);
            $this->merger->merge($spec, $openApi);
        }

        return $openApi;
    }

    // Goes through all the models like UpdatedFile, etc... and makes sure their schemas
    // have valid, dynamic attributes based on the attributes defined.
    private function addCustomAttributesToModels(OpenApi $openApi)
    {
        $fileAttributes = $this->app->make(FileCategory::class)->getList();
        $pageAttributes = $this->app->make(PageCategory::class)->getList();
        $userAttributes = $this->app->make(UserCategory::class)->getList();
        foreach ($openApi->components->schemas as $schema) {
            if (in_array($schema->schema, ['UpdatedFile', 'UpdatedUser', 'UpdatedPage'])) {

                $propertiesProperty = new SpecProperty('attributes', 'Attributes', 'object');
                switch($schema->schema) {
                    case 'UpdatedFile':
                        $attributesToAdd = $fileAttributes;
                        break;
                    case 'UpdatedUser':
                        $attributesToAdd = $userAttributes;
                        break;
                    case 'UpdatedPage':
                        $attributesToAdd = $pageAttributes;
                        break;
                }

                foreach ($attributesToAdd as $attribute) {
                    $handler = AttributeApiHandler::forController($attribute->getController());
                    $propertiesProperty->addObjectProperty($handler->getApiSpecProperty($attribute));
                }
                $this->merger->mergeProperty($propertiesProperty, $schema);
            }
        }
        return $openApi;
    }

    /**
     * The values that a field of a custom style takes are the ones this installation offers, and whoever offers
     * other ones puts a class of their own in place of StyleValues: so they are read from there, with the
     * empty value of a field nobody set beside them.
     *
     * The field naming a file says how a file is named here with the same words a field of the value of a
     * block uses, which is what its x-concrete-reference promises.
     */
    private function addCustomStyleValues(OpenApi $openApi)
    {
        $styleValues = $this->app->make(StyleValues::class);
        $lists = [
            'background_repeat' => $styleValues->getBackgroundRepeats(),
            'background_size' => $styleValues->getBackgroundSizes(),
            'background_position' => $styleValues->getBackgroundPositions(),
            'border_style' => $styleValues->getBorderStyles(),
            'alignment' => $styleValues->getAlignments(),
        ];
        foreach ($openApi->components->schemas as $schema) {
            if ($schema->schema !== 'CustomStyle' || !is_array($schema->properties)) {
                continue;
            }
            foreach ($schema->properties as $property) {
                if (isset($lists[$property->property])) {
                    $values = array_keys($lists[$property->property]);
                    $property->enum = in_array('', $values, true) ? $values : array_merge([''], $values);
                }
                if ($property->property === 'background_image') {
                    $property->description = $this->app->make(ReferenceCatalog::class)->describe(ReferenceKind::FILE);
                }
            }
        }

        return $openApi;
    }

    /**
     * The documentation lists the schemas in the order they are declared in, which puts the ones coming from
     * Express and from a package last: alphabetical is easier to look through, with the deprecated ones
     * after all the others.
     */
    private function sortSchemas(OpenApi $openApi): void
    {
        $components = $openApi->components;
        if (!$components instanceof Components || !is_array($components->schemas)) {
            return;
        }
        usort($components->schemas, function (Schema $a, Schema $b): int {
            if (($a->deprecated === true) !== ($b->deprecated === true)) {
                return $a->deprecated === true ? 1 : -1;
            }

            return strcasecmp($a->schema, $b->schema);
        });
    }

    public function getSpec(): OpenApi
    {
        $r = Generator::scan($this->sourceRegistry->getSources());
        $r = $this->addExpressSpec($r);
        $r = $this->addCustomAttributesToModels($r);
        $r = $this->addCustomStyleValues($r);

        $event = new GenerateApiSpecEvent($r);
        $this->eventDispatcher->dispatch('on_api_spec_generate', $event);
        $openApi = $event->getOpenApi();
        $this->sortSchemas($openApi);

        return $openApi;
    }

}
