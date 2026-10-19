<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="CustomStyle",
 *     type="object",
 *     title="CustomStyle",
 *     description="The custom style given to an area or to a block of a page, beyond what its theme does. A field with no value of its own is empty."
 * )
 */
class CustomStyle implements \JsonSerializable
{
    /**
     * @OA\Property(type="string", title="Custom class", description="Classes added to the HTML element. They go in the class attribute of the element wrapping the area or the block, beside the classes the page puts there itself. Several of them are separated by a space, and one carrying angle brackets or quotes is dropped.")
     *
     * @var string
     */
    public $custom_class;

    /**
     * @OA\Property(type="string", title="Custom ID", description="ID given to the HTML element. It goes in the id attribute of the element wrapping the area or the block.")
     *
     * @var string
     */
    public $custom_id;

    /**
     * @OA\Property(type="string", title="Custom element attribute", description="Attributes written on the HTML element. They are written inside the tag of the element wrapping the area or the block, as they are. The class and the id have fields of their own and are taken out of here, and the quotes have to be closed.")
     *
     * @var string
     */
    public $custom_element_attribute;

    /**
     * @OA\Property(type="string", title="Background color", description="A CSS color: #rgb, #rgba, #rrggbb or #rrggbbaa, rgb(), rgba(), hsl() or hsla() with its channels, transparent or currentColor. The name of a color is not one of them.")
     *
     * @var string
     */
    public $background_color;

    /**
     * @OA\Property(type="string", title="Background repeat", description="How the background image repeats. It holds together with the background color or the background image, and is empty without them.")
     *
     * @var string
     */
    public $background_repeat;

    /**
     * @OA\Property(type="string", title="Background size", description="Size of the background image. It holds together with the background color or the background image, and is empty without them.")
     *
     * @var string
     */
    public $background_size;

    /**
     * @OA\Property(type="string", title="Background position", description="Position of the background image. It holds together with the background color or the background image, and is empty without them.")
     *
     * @var string
     */
    public $background_position;

    /**
     * @OA\Property(type="string", title="Border color", description="A CSS color: #rgb, #rgba, #rrggbb or #rrggbbaa, rgb(), rgba(), hsl() or hsla() with its channels, transparent or currentColor. The name of a color is not one of them. It holds together with the border width.")
     *
     * @var string
     */
    public $border_color;

    /**
     * @OA\Property(type="string", title="Border style", description="It holds together with the border width.")
     *
     * @var string
     */
    public $border_style;

    /**
     * @OA\Property(type="string", title="Border width", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one is dropped. The style and the color of the border hold only with it.")
     *
     * @var string
     */
    public $border_width;

    /**
     * @OA\Property(type="string", title="Border radius", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one is dropped.")
     *
     * @var string
     */
    public $border_radius;

    /**
     * @OA\Property(type="string", title="Base font size", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one is dropped.")
     *
     * @var string
     */
    public $base_font_size;

    /**
     * @OA\Property(type="string", title="Text alignment", description="How the contents are aligned: empty leaves them to the theme.")
     *
     * @var string
     */
    public $alignment;

    /**
     * @OA\Property(type="string", title="Text color", description="A CSS color: #rgb, #rgba, #rrggbb or #rrggbbaa, rgb(), rgba(), hsl() or hsla() with its channels, transparent or currentColor. The name of a color is not one of them.")
     *
     * @var string
     */
    public $text_color;

    /**
     * @OA\Property(type="string", title="Color of the links", description="A CSS color: #rgb, #rgba, #rrggbb or #rrggbbaa, rgb(), rgba(), hsl() or hsla() with its channels, transparent or currentColor. The name of a color is not one of them.")
     *
     * @var string
     */
    public $link_color;

    /**
     * @OA\Property(type="string", title="Margin at the top", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one holds.")
     *
     * @var string
     */
    public $margin_top;

    /**
     * @OA\Property(type="string", title="Margin at the bottom", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one holds.")
     *
     * @var string
     */
    public $margin_bottom;

    /**
     * @OA\Property(type="string", title="Margin on the left", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one holds.")
     *
     * @var string
     */
    public $margin_left;

    /**
     * @OA\Property(type="string", title="Margin on the right", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one holds.")
     *
     * @var string
     */
    public $margin_right;

    /**
     * @OA\Property(type="string", title="Padding at the top", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one is dropped.")
     *
     * @var string
     */
    public $padding_top;

    /**
     * @OA\Property(type="string", title="Padding at the bottom", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one is dropped.")
     *
     * @var string
     */
    public $padding_bottom;

    /**
     * @OA\Property(type="string", title="Padding on the left", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one is dropped.")
     *
     * @var string
     */
    public $padding_left;

    /**
     * @OA\Property(type="string", title="Padding on the right", description="A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one is dropped.")
     *
     * @var string
     */
    public $padding_right;

    /**
     * @OA\Property(type="string", title="Rotation", description="The degrees to turn the element by, a plain number with no unit: 45 turns it a quarter clockwise, -45 the other way.")
     *
     * @var string
     */
    public $rotate;

    /**
     * @OA\Property(type="string", title="Horizontal offset", description="Horizontal offset of the box shadow. A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one holds. The shadow is drawn where one of its four lengths is not zero.")
     *
     * @var string
     */
    public $box_shadow_horizontal;

    /**
     * @OA\Property(type="string", title="Vertical offset", description="Vertical offset of the box shadow. A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one holds. The shadow is drawn where one of its four lengths is not zero.")
     *
     * @var string
     */
    public $box_shadow_vertical;

    /**
     * @OA\Property(type="string", title="Blur radius", description="Blur radius of the box shadow. A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one is dropped. The shadow is drawn where one of its four lengths is not zero.")
     *
     * @var string
     */
    public $box_shadow_blur;

    /**
     * @OA\Property(type="string", title="Spread radius", description="Spread radius of the box shadow. A number with its unit, one of px, %, em, rem, vh, vw, vmin and vmax. A negative one holds. The shadow is drawn where one of its four lengths is not zero.")
     *
     * @var string
     */
    public $box_shadow_spread;

    /**
     * @OA\Property(type="string", title="Shadow color", description="A CSS color: #rgb, #rgba, #rrggbb or #rrggbbaa, rgb(), rgba(), hsl() or hsla() with its channels, transparent or currentColor. The name of a color is not one of them.")
     *
     * @var string
     */
    public $box_shadow_color;

    /**
     * @OA\Property(
     *     nullable=true,
     *     title="Background image",
     *     oneOf={@OA\Schema(type="string"), @OA\Schema(type="integer")},
     *     x={"concrete-reference": \Concrete\Core\Reference\ReferenceKind::FILE}
     * )
     *
     * @var int|string|null
     */
    public $background_image;

    /**
     * @OA\Property(type="boolean", title="Inset?", description="Whether the shadow is drawn inside the element instead of around it.")
     *
     * @var bool
     */
    public $box_shadow_inset = false;

    /**
     * @OA\Property(type="boolean", title="Hidden on extra small devices?", description="Which widths are the smallest ones is up to the grid framework of the theme the page is shown with, which GET /page_themes names.")
     *
     * @var bool
     */
    public $hide_on_extra_small_device = false;

    /**
     * @OA\Property(type="boolean", title="Hidden on small devices?", description="Which widths are the small ones is up to the grid framework of the theme the page is shown with, which GET /page_themes names.")
     *
     * @var bool
     */
    public $hide_on_small_device = false;

    /**
     * @OA\Property(type="boolean", title="Hidden on medium devices?", description="Which widths are the medium ones is up to the grid framework of the theme the page is shown with, which GET /page_themes names.")
     *
     * @var bool
     */
    public $hide_on_medium_device = false;

    /**
     * @OA\Property(type="boolean", title="Hidden on large devices?", description="Which widths are the large ones is up to the grid framework of the theme the page is shown with, which GET /page_themes names.")
     *
     * @var bool
     */
    public $hide_on_large_device = false;

    /**
     * {@inheritdoc}
     *
     * @see \JsonSerializable::jsonSerialize()
     *
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}
