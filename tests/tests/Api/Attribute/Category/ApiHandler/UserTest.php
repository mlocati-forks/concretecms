<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute\Category\ApiHandler;

use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Attribute\Category\CategoryInterface;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Attribute\Category\ApiHandler\User
 */
class UserTest extends TestCase
{
    public function testTheKeysOfThisCategorySayWhatTheyBelongTo(): void
    {
        $handler = new ApiHandler\User($this->createMock(CategoryInterface::class));

        static::assertSame('Attributes of users', $handler->getCategoryDescription());
    }
}
