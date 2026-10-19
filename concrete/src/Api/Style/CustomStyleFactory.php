<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Style;

use Concrete\Core\Api\FileIdentifier;
use Concrete\Core\Entity\StyleCustomizer\Inline\StyleSet;
use Concrete\Core\StyleCustomizer\Inline\StyleValues;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The custom style that a client asks for an area or for a block.
 *
 * What a request says is held to the same rules the design dialog holds a form to, since both come from
 * outside: a value that is not a CSS value of its kind is dropped instead of being stored.
 *
 * @see \Concrete\Core\StyleCustomizer\Inline\StyleValues the values a field takes, and the rules they are held to
 */
class CustomStyleFactory
{
    /**
     * Every field a client writes, by the setter of the style set taking it.
     *
     * @var array<string, string>
     */
    private const SETTERS = [
        'custom_class' => 'setCustomClass',
        'custom_id' => 'setCustomID',
        'custom_element_attribute' => 'setCustomElementAttribute',
        'background_color' => 'setBackgroundColor',
        'background_image' => 'setBackgroundImageFileID',
        'background_repeat' => 'setBackgroundRepeat',
        'background_size' => 'setBackgroundSize',
        'background_position' => 'setBackgroundPosition',
        'border_color' => 'setBorderColor',
        'border_style' => 'setBorderStyle',
        'border_width' => 'setBorderWidth',
        'border_radius' => 'setBorderRadius',
        'base_font_size' => 'setBaseFontSize',
        'alignment' => 'setAlignment',
        'text_color' => 'setTextColor',
        'link_color' => 'setLinkColor',
        'margin_top' => 'setMarginTop',
        'margin_bottom' => 'setMarginBottom',
        'margin_left' => 'setMarginLeft',
        'margin_right' => 'setMarginRight',
        'padding_top' => 'setPaddingTop',
        'padding_bottom' => 'setPaddingBottom',
        'padding_left' => 'setPaddingLeft',
        'padding_right' => 'setPaddingRight',
        'rotate' => 'setRotate',
        'box_shadow_horizontal' => 'setBoxShadowHorizontal',
        'box_shadow_vertical' => 'setBoxShadowVertical',
        'box_shadow_blur' => 'setBoxShadowBlur',
        'box_shadow_spread' => 'setBoxShadowSpread',
        'box_shadow_color' => 'setBoxShadowColor',
        'box_shadow_inset' => 'setBoxShadowInset',
        'hide_on_extra_small_device' => 'setHideOnExtraSmallDevice',
        'hide_on_small_device' => 'setHideOnSmallDevice',
        'hide_on_medium_device' => 'setHideOnMediumDevice',
        'hide_on_large_device' => 'setHideOnLargeDevice',
    ];

    /**
     * The fields holding a color.
     *
     * @var string[]
     */
    private const COLORS = [
        'background_color',
        'border_color',
        'text_color',
        'link_color',
        'box_shadow_color',
    ];

    /**
     * The fields holding a length, and whether a negative one holds for each.
     *
     * @var array<string, bool>
     */
    private const LENGTHS = [
        'base_font_size' => false,
        'margin_top' => true,
        'margin_bottom' => true,
        'margin_left' => true,
        'margin_right' => true,
        'padding_top' => false,
        'padding_bottom' => false,
        'padding_left' => false,
        'padding_right' => false,
        'border_width' => false,
        'border_radius' => false,
        'box_shadow_horizontal' => true,
        'box_shadow_vertical' => true,
        'box_shadow_blur' => false,
        'box_shadow_spread' => true,
    ];

    /**
     * The fields holding one of a few values, by the method of StyleValues answering for each.
     *
     * @var array<string, string>
     */
    private const ENUMS = [
        'background_repeat' => 'sanitizeBackgroundRepeat',
        'background_size' => 'sanitizeBackgroundSize',
        'background_position' => 'sanitizeBackgroundPosition',
        'border_style' => 'sanitizeBorderStyle',
        'alignment' => 'sanitizeAlignment',
    ];

    /**
     * The fields holding a flag.
     *
     * @var string[]
     */
    private const FLAGS = [
        'box_shadow_inset',
        'hide_on_extra_small_device',
        'hide_on_small_device',
        'hide_on_medium_device',
        'hide_on_large_device',
    ];

    /**
     * The sizes a shadow is drawn with, by the value it takes where the request says nothing of it.
     *
     * @var array<string, string>
     */
    private const BOX_SHADOW_SIZES = [
        'box_shadow_horizontal' => '0px',
        'box_shadow_vertical' => '0px',
        'box_shadow_blur' => '0px',
        'box_shadow_spread' => '0px',
    ];

    /**
     * The values the three fields saying how a background image is drawn take where it holds and they
     * say nothing.
     *
     * @var array<string, string>
     */
    private const BACKGROUND_DEFAULTS = [
        'background_repeat' => 'no-repeat',
        'background_size' => 'auto',
        'background_position' => 'left top',
    ];

    /**
     * @var \Concrete\Core\Api\FileIdentifier
     */
    protected $fileIdentifier;

    /**
     * @var \Concrete\Core\StyleCustomizer\Inline\StyleValues
     */
    protected $styleValues;

    public function __construct(FileIdentifier $fileIdentifier, StyleValues $styleValues)
    {
        $this->fileIdentifier = $fileIdentifier;
        $this->styleValues = $styleValues;
    }

    /**
     * Build the custom style a request asks for, NULL where it asks for nothing that holds: an area or a
     * block given that is left to the theme alone.
     *
     * @param array<string,mixed> $fields
     *
     * @throws \Concrete\Core\Error\UserMessageException when a custom element attribute has unclosed quotes
     */
    public function create(array $fields): ?StyleSet
    {
        $values = $this->keepWhatHolds($this->sanitize($fields));
        if ($values === []) {
            return null;
        }
        $styleSet = new StyleSet();
        foreach ($values as $field => $value) {
            $styleSet->{self::SETTERS[$field]}($value);
        }

        return $styleSet;
    }

    /**
     * Hold every field of a request to the rules of its kind, each field on its own.
     *
     * @param array<string,mixed> $fields
     *
     * @return array<string,mixed> every field, worth nothing where the request says nothing that holds
     *
     * @throws \Concrete\Core\Error\UserMessageException when a custom element attribute has unclosed quotes
     */
    private function sanitize(array $fields): array
    {
        $values = [];
        foreach (self::COLORS as $field) {
            $values[$field] = $this->styleValues->sanitizeColor($this->read($fields, $field));
        }
        foreach (self::LENGTHS as $field => $allowNegative) {
            $values[$field] = $this->styleValues->sanitizeLength($this->read($fields, $field), $allowNegative, false);
        }
        foreach (self::ENUMS as $field => $sanitizer) {
            $values[$field] = $this->styleValues->{$sanitizer}($this->read($fields, $field));
        }
        foreach (self::FLAGS as $field) {
            $values[$field] = (bool) $this->read($fields, $field);
        }
        $values['rotate'] = $this->styleValues->sanitizeRotation($this->read($fields, 'rotate'), false);
        $values['custom_class'] = $this->styleValues->sanitizeClasses(
            preg_split('/\s+/', trim((string) $this->read($fields, 'custom_class')), -1, PREG_SPLIT_NO_EMPTY) ?: []
        );
        $values['custom_id'] = trim((string) $this->read($fields, 'custom_id'));
        $values['custom_element_attribute'] = $this->styleValues->sanitizeElementAttribute($this->read($fields, 'custom_element_attribute'));
        $values['background_image'] = $this->fileIdentifier->fromApi($this->read($fields, 'background_image'));

        return $values;
    }

    /**
     * Some fields hold for another one and not on their own, as they do in the design dialog: what is
     * left without its company goes, and so does whatever holds nothing.
     *
     * @param array<string,mixed> $values
     *
     * @return array<string,mixed> the fields to write, and nothing else
     */
    private function keepWhatHolds(array $values): array
    {
        if ($values['background_color'] === '' && $values['background_image'] <= 0) {
            $values = array_merge($values, array_fill_keys(array_keys(self::BACKGROUND_DEFAULTS), ''));
        } else {
            foreach (self::BACKGROUND_DEFAULTS as $field => $default) {
                $values[$field] = $values[$field] ?: $default;
            }
        }
        if ($values['border_width'] === '') {
            $values['border_style'] = '';
            $values['border_color'] = '';
        }
        // a shadow of no size at all is no shadow
        if (array_filter(array_intersect_key($values, self::BOX_SHADOW_SIZES)) === []) {
            $values = array_merge($values, array_fill_keys(array_keys(self::BOX_SHADOW_SIZES), ''));
            $values['box_shadow_color'] = '';
            $values['box_shadow_inset'] = false;
        } else {
            foreach (self::BOX_SHADOW_SIZES as $field => $default) {
                $values[$field] = $values[$field] ?: $default;
            }
        }

        // a class or an id can be worth the string zero, so what goes is what holds nothing of its kind
        return array_filter($values, static function ($value) {
            return $value !== '' && $value !== false && $value !== 0;
        });
    }

    /**
     * @param array<string,mixed> $fields
     *
     * @return mixed NULL where the request says nothing of the field
     */
    private function read(array $fields, string $field)
    {
        return $fields[$field] ?? null;
    }
}
