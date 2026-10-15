<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\BlockType as BlockTypeModel;
use Concrete\Core\Api\Model\BlockType\CustomTemplate as CustomTemplateModel;
use Concrete\Core\Block\BlockType\TemplateCatalog;
use Concrete\Core\Filesystem\TemplateFile;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Page\Theme\Theme;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class BlockTypeTransformer extends TransformerAbstract
{
    /**
     * @var \Concrete\Core\Page\Theme\Theme[]|null
     */
    private $themes;

    /**
     * @var \Concrete\Core\Block\BlockType\TemplateCatalog|null
     */
    private $catalog;

    /**
     * @return array<string,mixed>
     */
    public function transform(BlockType $blockType): array
    {
        $controller = $blockType->getController();
        $handler = $controller === null ? null : $controller->getApiHandler();
        $description = $handler === null ? '' : $handler->getCustomApiDescription();
        if ($description === '') {
            $description = (string) $blockType->getBlockTypeDescription();
        }

        $model = new BlockTypeModel();
        $model->handle = (string) $blockType->getBlockTypeHandle();
        $model->name = (string) $blockType->getBlockTypeName();
        $model->description = $description;
        $model->package = (string) $blockType->getPackageHandle();
        $model->value_schema = $handler === null ? null : $handler->getApiValueSchema();
        $model->custom_templates = $this->getCustomTemplates($blockType);

        return $model->jsonSerialize();
    }

    /**
     * Get the catalog of the templates: one instance for the whole list, since every block type asks it
     * for the installed packages.
     */
    protected function getCatalog(): TemplateCatalog
    {
        if ($this->catalog === null) {
            $this->catalog = app(TemplateCatalog::class);
        }

        return $this->catalog;
    }

    /**
     * Get the themes a page of this installation can be shown with.
     *
     * @return \Concrete\Core\Page\Theme\Theme[]
     */
    protected function getThemes(): array
    {
        if ($this->themes === null) {
            $this->themes = Theme::getList();
        }

        return $this->themes;
    }

    /**
     * Get the custom templates of a block type as a client reads them: one entry per name it can write,
     * naming the themes that give it, or no theme at all when every page has it.
     *
     * @return \Concrete\Core\Api\Model\BlockType\CustomTemplate[]
     */
    private function getCustomTemplates(BlockType $blockType): array
    {
        $catalog = $this->getCatalog();
        $models = [];
        foreach ($catalog->getCustomTemplates($blockType, null) as $template) {
            $models[$template->getTemplateFileFilename()] = $this->describeCustomTemplate($template);
        }
        $ofThemes = [];
        foreach ($this->getThemes() as $theme) {
            foreach ($catalog->getThemeCustomTemplates($blockType, $theme) as $template) {
                $handle = $template->getTemplateFileFilename();
                if (isset($models[$handle])) {
                    continue;
                }
                if (!isset($ofThemes[$handle])) {
                    $ofThemes[$handle] = $this->describeCustomTemplate($template);
                }
                $ofThemes[$handle]->only_for_themes[] = (string) $theme->getThemeHandle();
            }
        }
        $models = array_values(array_merge($models, $ofThemes));
        usort($models, static function (CustomTemplateModel $a, CustomTemplateModel $b): int {
            return strcasecmp($a->name, $b->name) ?: strcmp($a->handle, $b->handle);
        });

        return $models;
    }

    private function describeCustomTemplate(TemplateFile $template): CustomTemplateModel
    {
        $model = new CustomTemplateModel();
        $model->handle = $template->getTemplateFileFilename();
        $model->name = $template->getTemplateFileName();

        return $model;
    }
}
