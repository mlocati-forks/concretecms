<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Style;

use Concrete\Core\Api\FileIdentifier;
use Concrete\Core\Api\Model\CustomStyle as CustomStyleModel;
use Concrete\Core\Entity\StyleCustomizer\Inline\StyleSet;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The custom style of an area or of a block, as a client reads it.
 */
class CustomStyleDescriber
{
    /**
     * The fields a client reads, by the getter of the style set answering for each: every one of them
     * holds a CSS value the theme of the page knows nothing about.
     *
     * @var array<string, string>
     */
    private const TEXTS = [
        'custom_class' => 'getCustomClass',
        'custom_id' => 'getCustomID',
        'custom_element_attribute' => 'getCustomElementAttribute',
        'background_color' => 'getBackgroundColor',
        'background_repeat' => 'getBackgroundRepeat',
        'background_size' => 'getBackgroundSize',
        'background_position' => 'getBackgroundPosition',
        'border_color' => 'getBorderColor',
        'border_style' => 'getBorderStyle',
        'border_width' => 'getBorderWidth',
        'border_radius' => 'getBorderRadius',
        'base_font_size' => 'getBaseFontSize',
        'alignment' => 'getAlignment',
        'text_color' => 'getTextColor',
        'link_color' => 'getLinkColor',
        'margin_top' => 'getMarginTop',
        'margin_bottom' => 'getMarginBottom',
        'margin_left' => 'getMarginLeft',
        'margin_right' => 'getMarginRight',
        'padding_top' => 'getPaddingTop',
        'padding_bottom' => 'getPaddingBottom',
        'padding_left' => 'getPaddingLeft',
        'padding_right' => 'getPaddingRight',
        'rotate' => 'getRotate',
        'box_shadow_horizontal' => 'getBoxShadowHorizontal',
        'box_shadow_vertical' => 'getBoxShadowVertical',
        'box_shadow_blur' => 'getBoxShadowBlur',
        'box_shadow_spread' => 'getBoxShadowSpread',
        'box_shadow_color' => 'getBoxShadowColor',
    ];

    /**
     * The field holding a file, which travels by UUID the way the files of this API do.
     *
     * @var array<string, string>
     */
    private const FILES = [
        'background_image' => 'getBackgroundImageFileID',
    ];

    /**
     * @var array<string, string>
     */
    private const FLAGS = [
        'box_shadow_inset' => 'getBoxShadowInset',
        'hide_on_extra_small_device' => 'getHideOnExtraSmallDevice',
        'hide_on_small_device' => 'getHideOnSmallDevice',
        'hide_on_medium_device' => 'getHideOnMediumDevice',
        'hide_on_large_device' => 'getHideOnLargeDevice',
    ];

    /**
     * @var \Concrete\Core\Api\FileIdentifier
     */
    protected $fileIdentifier;

    public function __construct(FileIdentifier $fileIdentifier)
    {
        $this->fileIdentifier = $fileIdentifier;
    }

    /**
     * Describe the custom style of an area or of a block.
     */
    public function describe(StyleSet $styleSet): CustomStyleModel
    {
        $model = new CustomStyleModel();
        foreach (self::TEXTS as $field => $getter) {
            $model->{$field} = (string) $styleSet->{$getter}();
        }
        foreach (self::FLAGS as $field => $getter) {
            $model->{$field} = (bool) $styleSet->{$getter}();
        }
        foreach (self::FILES as $field => $getter) {
            // a style set holds 0 where it names no file, and a client reads NULL for the same thing
            $fileID = (int) $styleSet->{$getter}();
            $model->{$field} = $fileID > 0 ? $this->fileIdentifier->forApi($fileID) : null;
        }

        return $model;
    }
}
