<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute\Category\ApiHandler;

use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Attribute\Category\CategoryInterface;
use Concrete\Core\Entity\Attribute\Key\UserKey;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Where the site asks for an attribute of a user and where it shows it belongs to the category, not
 * to the type of the key, so the category is the one that hands it over.
 *
 * @see \Concrete\Core\Api\Attribute\Category\ApiHandler\User
 */
class UserTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAKeySaysWhereTheSiteAsksForItAndWhereItShowsIt(): void
    {
        $fields = $this->getFields(['isAttributeKeyRequiredOnRegister' => true, 'isAttributeKeyDisplayedOnProfile' => true]);

        static::assertAnswerIs([
            'editable_on_register' => false,
            'required_on_register' => true,
            'editable_on_profile' => false,
            'required_on_profile' => false,
            'displayed_on_profile' => true,
            'displayed_on_member_list' => false,
        ], $fields['user']);
    }

    /**
     * The category names what it adds after itself, so that a client reads it by the category of the key.
     */
    public function testWhatTheCategoryAddsIsNamedAfterIt(): void
    {
        static::assertSame(['user'], array_keys($this->getFields([])));
    }

    public function testTheFieldsAreTheOnesTheSpecificationDescribes(): void
    {
        $this->assertFieldsAre('AttributeKeyUser', $this->getFields([])['user']);
    }

    public function testTheKeysOfThisCategorySayWhatTheyBelongTo(): void
    {
        $handler = new ApiHandler\User($this->createMock(CategoryInterface::class));

        static::assertSame('Attributes of users', $handler->getCategoryDescription());
    }

    /**
     * @param array<string,bool> $flags the flags of the key that are on, by the name of their getter
     *
     * @return array<string,mixed> what the category adds to what a client reads of the key
     */
    private function getFields(array $flags): array
    {
        $key = $this->createMock(UserKey::class);
        foreach ([
            'isAttributeKeyEditableOnRegister',
            'isAttributeKeyRequiredOnRegister',
            'isAttributeKeyEditableOnProfile',
            'isAttributeKeyRequiredOnProfile',
            'isAttributeKeyDisplayedOnProfile',
            'isAttributeKeyDisplayedOnMemberList',
        ] as $getter) {
            $key->method($getter)->willReturn($flags[$getter] ?? false);
        }
        $handler = new ApiHandler\User($this->createMock(CategoryInterface::class));

        return $handler->getApiKeyFields($key);
    }
}
