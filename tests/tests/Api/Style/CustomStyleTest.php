<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Style;

use Concrete\Core\Api\Fractal\Transformer\AreaTransformer;
use Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer;
use Concrete\Core\Api\Model\CustomStyle as CustomStyleModel;
use Concrete\Core\Api\Style\CustomStyleDescriber;
use Concrete\Core\Api\Style\CustomStyleFactory;
use Concrete\Core\Area\ApiArea;
use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Entity\StyleCustomizer\Inline\StyleSet;
use Concrete\Core\Page\Page;
use Concrete\Core\User\User;
use Concrete\TestHelpers\Api\TokenScopesTrait;
use Concrete\TestHelpers\Block\BlockApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The custom style given to an area or to a block, beyond what the theme of the page does, as a client
 * reads it.
 *
 * @see \Concrete\Core\Api\Style\CustomStyleDescriber
 */
class CustomStyleTest extends BlockApiTestCase
{
    use TokenScopesTrait;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Block\BlockApiTestCase::setUp()
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->giveTheTokenScopes(['pages:read', 'blocks:read']);
        // with no permission key registered, only the superuser is allowed anything
        $superUser = $this->createMock(User::class);
        $superUser->method('isSuperUser')->willReturn(true);
        app()->instance(User::class, $superUser);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Block\BlockApiTestCase::tearDown()
     */
    public function tearDown(): void
    {
        $this->takeTheTokenBack();
        app()->forgetInstance(User::class);
        parent::tearDown();
    }

    public function testABlockWithNoCustomStyleOfItsOwnHasNone(): void
    {
        $transformed = $this->transformBlock($this->addBlock('page_title', []));

        static::assertNull($transformed['custom_style']);
    }

    public function testTheCustomStyleOfABlockTravelsWithIt(): void
    {
        $page = self::createPage('Page with a block of its own custom style');
        $block = $this->addBlock('page_title', [], $page);
        $block->setCustomStyleSet($this->createStyleSet([
            'setCustomClass' => 'hero dark',
            'setPaddingTop' => '50px',
            'setHideOnSmallDevice' => true,
        ]));

        $style = $this->transformBlock($this->getBlock($page))['custom_style'];

        static::assertSame('hero dark', $style->custom_class);
        static::assertSame('50px', $style->padding_top);
        static::assertTrue($style->hide_on_small_device);
        static::assertFalse($style->hide_on_large_device);
    }

    /**
     * The design dialog saves an empty string for a field nobody touched, and a style set made elsewhere
     * leaves it NULL: a client reads the same empty string for both.
     */
    public function testAFieldWithNothingInItIsNothing(): void
    {
        $page = self::createPage('Page with a block whose custom style says little');
        $block = $this->addBlock('page_title', [], $page);
        $block->setCustomStyleSet($this->createStyleSet(['setCustomClass' => '', 'setBackgroundColor' => 'rgba(0,0,0,1)']));

        $style = $this->transformBlock($this->getBlock($page))['custom_style'];

        static::assertSame('', $style->custom_class);
        static::assertSame('', $style->margin_top);
        static::assertSame('rgba(0,0,0,1)', $style->background_color);
    }

    public function testTheCustomStyleOfAnAreaTravelsWithIt(): void
    {
        $page = self::createPage('Page with an area of its own custom style');
        $area = Area::getOrCreate($page, 'Main');
        $page->setCustomStyleSet($area, $this->createStyleSet(['setBackgroundColor' => 'rgba(255,0,0,1)']));

        $style = $this->transformArea($page, 'Main')['custom_style'];

        static::assertSame('rgba(255,0,0,1)', $style->background_color);
    }

    public function testAnAreaWithNoCustomStyleOfItsOwnHasNone(): void
    {
        $page = self::createPage('Page with an area with no custom style');
        Area::getOrCreate($page, 'Main');

        static::assertNull($this->transformArea($page, 'Main')['custom_style']);
    }

    /**
     * A file travels by UUID here as everywhere else in this API.
     */
    public function testTheBackgroundImageOfACustomStyleTravelsByUuid(): void
    {
        $page = self::createPage('Page with a block behind an image');
        $block = $this->addBlock('page_title', [], $page);
        $file = $this->getFile();
        $block->setCustomStyleSet($this->createStyleSet(['setBackgroundImageFileID' => $file->getFileID()]));

        $style = $this->transformBlock($this->getBlock($page))['custom_style'];

        static::assertSame((string) $file->getFileUUID(), $style->background_image);
    }

    /**
     * Whoever adds a field to a style set has to hand it over here as well, and this says so.
     */
    public function testEveryFieldOfAStyleSetIsHandedOver(): void
    {
        $maps = (new \ReflectionClass(CustomStyleDescriber::class))->getConstants();
        $getters = [];
        $fields = [];
        foreach (['TEXTS', 'FILES', 'FLAGS'] as $name) {
            static::assertArrayHasKey($name, $maps);
            foreach ($maps[$name] as $field => $getter) {
                $fields[] = $field;
                $getters[] = $getter;
            }
        }
        $metadata = app(EntityManagerInterface::class)->getClassMetadata(StyleSet::class);
        $identifiers = $metadata->getIdentifierFieldNames();
        $expected = [];
        foreach ($metadata->getFieldNames() as $column) {
            // the ID of a style set is its own business: a write makes a new set of its own
            if (!in_array($column, $identifiers, true)) {
                $expected[] = 'get' . ucfirst($column);
            }
        }
        sort($expected);
        sort($getters);
        static::assertSame(
            $expected,
            $getters,
            'A field of a style set is missing from CustomStyleDescriber, or one it reads is gone: put the new'
            . ' ones in its TEXTS, FILES or FLAGS, beside a field of the CustomStyle model, and take out the rest'
        );

        $properties = [];
        foreach ((new \ReflectionClass(CustomStyleModel::class))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $properties[] = $property->getName();
        }
        sort($fields);
        sort($properties);
        static::assertSame(
            $properties,
            $fields,
            'CustomStyleDescriber and the CustomStyle model name different fields: each field of its maps is a'
            . ' public property of the model, and each property of the model is read by one of its maps'
        );
    }

    /**
     * Whoever adds a field to a style set has to teach the factory to write it: this says so by itself,
     * without waiting for a value to come back wrong.
     *
     * @see \Concrete\Core\Api\Style\CustomStyleFactory::create()
     */
    public function testEveryFieldOfACustomStyleIsOneTheFactoryWrites(): void
    {
        $setters = (new \ReflectionClass(CustomStyleFactory::class))->getConstant('SETTERS');
        static::assertIsArray($setters);
        $fields = array_keys($setters);
        $properties = [];
        foreach ((new \ReflectionClass(CustomStyleModel::class))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $properties[] = $property->getName();
        }
        sort($fields);
        sort($properties);
        static::assertSame(
            $properties,
            $fields,
            'CustomStyleFactory writes the fields of the CustomStyle model and no others: a field of the'
            . ' model missing from its SETTERS is one a client cannot write, and one in SETTERS that the'
            . ' model has not is one a client cannot read'
        );

        $styleSet = new StyleSet();
        foreach ($setters as $field => $setter) {
            static::assertTrue(
                method_exists($styleSet, $setter),
                'a style set takes the field ' . $field . ' through ' . $setter . '(), which it has not'
            );
        }
    }

    /**
     * A custom style a client writes is one a client reads: this fails when a field can be read and not
     * written, which is what happens to whoever adds one to the model without teaching the factory.
     *
     * @see \Concrete\Core\Api\Style\CustomStyleFactory::create()
     */
    public function testEveryFieldOfACustomStyleIsWrittenAsItIsRead(): void
    {
        $fields = [
            'custom_class' => 'hero dark',
            'custom_id' => 'hero-1',
            'custom_element_attribute' => 'data-x="1"',
            'background_color' => 'rgba(1,2,3,1)',
            'background_image' => (string) $this->getFile()->getFileUUID(),
            'background_repeat' => 'repeat-x',
            'background_size' => 'cover',
            'background_position' => 'center center',
            'border_color' => 'rgba(4,5,6,1)',
            'border_style' => 'dotted',
            'border_width' => '3px',
            'border_radius' => '4px',
            'base_font_size' => '15px',
            'alignment' => 'right',
            'text_color' => '#ff0000',
            'link_color' => '#00ff00',
            'margin_top' => '5px',
            'margin_bottom' => '-6px',
            'margin_left' => '7px',
            'margin_right' => '8px',
            'padding_top' => '9px',
            'padding_bottom' => '10px',
            'padding_left' => '11px',
            'padding_right' => '12px',
            'rotate' => '-3',
            'box_shadow_horizontal' => '13px',
            'box_shadow_vertical' => '-14px',
            'box_shadow_blur' => '15px',
            'box_shadow_spread' => '16px',
            'box_shadow_color' => 'rgba(7,8,9,1)',
            'box_shadow_inset' => true,
            'hide_on_extra_small_device' => true,
            'hide_on_small_device' => true,
            'hide_on_medium_device' => true,
            'hide_on_large_device' => true,
        ];

        $styleSet = app(CustomStyleFactory::class)->create($fields);

        static::assertNotNull($styleSet, 'a custom style with a value in every field of it holds');
        $read = app(CustomStyleDescriber::class)->describe($styleSet)->jsonSerialize();
        ksort($fields);
        ksort($read);
        static::assertSame(
            $fields,
            $read,
            'A field written by CustomStyleFactory comes back from CustomStyleDescriber as it was written:'
            . ' a field missing from this answer is one the factory does not write, and one that came back'
            . ' changed is a rule of the factory this test does not know about'
        );
    }

    /**
     * A field that holds nothing is not written, and the string zero holds: it is a class, or an id, worth
     * the character zero.
     */
    public function testAFieldWorthTheStringZeroIsWritten(): void
    {
        $styleSet = app(CustomStyleFactory::class)->create(['custom_class' => '0', 'custom_id' => '0']);

        static::assertNotNull($styleSet);
        static::assertSame('0', $styleSet->getCustomClass());
        static::assertSame('0', $styleSet->getCustomID());
    }

    /**
     * @param array<string,mixed> $values what to set on the style set, by the name of the setter
     */
    private function createStyleSet(array $values): StyleSet
    {
        $styleSet = new StyleSet();
        foreach ($values as $setter => $value) {
            $styleSet->{$setter}($value);
        }
        $entityManager = app(EntityManagerInterface::class);
        $entityManager->persist($styleSet);
        $entityManager->flush();

        return $styleSet;
    }

    /**
     * @return array<string,mixed>
     */
    private function transformBlock(Block $block): array
    {
        return (new BaseBlockTransformer())->transform($block);
    }

    /**
     * @return array<string,mixed>
     */
    private function transformArea(Page $page, string $areaHandle): array
    {
        $page = Page::getByID($page->getCollectionID(), 'RECENT');

        return (new AreaTransformer())->transform(new ApiArea($page, $areaHandle));
    }
}
