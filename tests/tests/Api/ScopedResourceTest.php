<?php

declare(strict_types=1);

namespace Concrete\Tests\Api;

use Concrete\Core\Api\Fractal\Transformer\ExpressEntryTransformer;
use Concrete\Core\Api\Fractal\Transformer\FileSetTransformer;
use Concrete\Core\Api\Fractal\Transformer\UserTransformer;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\Entry;
use Concrete\Core\File\Set\Set;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Concrete\TestHelpers\Api\TokenScopesTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A thing of this installation travels whole where the token of the request carries the scope of the
 * endpoint serving the things of its kind, and named and nothing else where it doesn't: what names it
 * is also what a write takes, so a client can put it back where it found it.
 *
 * @see \Concrete\Core\Api\TokenScopes::has()
 */
class ScopedResourceTest extends TestCase
{
    use TokenScopesTrait;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Tests\TestCase::tearDown()
     */
    public function tearDown(): void
    {
        $this->takeTheTokenBack();
        app()->forgetInstance(User::class);
        parent::tearDown();
    }

    public function testAThingTravelsWholeWithTheScopeOfItsKind(): void
    {
        $this->giveTheTokenScopes(['files:read']);

        $transformed = (new FileSetTransformer())->transform($this->createSet());

        static::assertSame('Portfolio', $transformed['name']);
    }

    public function testAThingIsNamedAndNothingElseWithoutIt(): void
    {
        $this->giveTheTokenScopes(['pages:read']);

        static::assertSame(['id' => 12], (new FileSetTransformer())->transform($this->createSet()));
    }

    public function testAnotherUserIsNamedAndNothingElse(): void
    {
        $this->giveTheTokenScopes(['pages:read']);
        $this->signIn(9);

        static::assertSame(['id' => 7], (new UserTransformer())->transform($this->createUser(7)));
    }

    public function testTheUserTheTokenBelongsToTravelsWhole(): void
    {
        $this->giveTheTokenScopes(['pages:read']);
        $this->signIn(7);

        $transformed = (new UserTransformer())->transform($this->createUser(7));

        static::assertArrayHasKey('username', $transformed);
    }

    public function testAnEntryIsNamedAndNothingElseWithoutTheScopeOfItsEntity(): void
    {
        $this->giveTheTokenScopes(['clients:read']);
        $entity = $this->createMock(Entity::class);
        $entity->method('getPluralHandle')->willReturn('invoices');
        $entry = $this->createMock(Entry::class);
        $entry->method('getPublicIdentifier')->willReturn('7b2-1f');

        static::assertSame(['id' => '7b2-1f'], (new ExpressEntryTransformer($entity))->transform($entry));
    }

    private function signIn(int $userID): void
    {
        $user = $this->createMock(User::class);
        $user->method('getUserID')->willReturn($userID);
        app()->instance(User::class, $user);
    }

    private function createSet(): Set
    {
        $set = $this->createMock(Set::class);
        $set->method('getFileSetID')->willReturn(12);
        $set->method('getFileSetName')->willReturn('Portfolio');
        $set->method('getFileSetType')->willReturn(Set::TYPE_PUBLIC);

        return $set;
    }

    private function createUser(int $userID): UserInfo
    {
        $avatar = $this->createMock(\Concrete\Core\User\Avatar\AvatarInterface::class);
        $avatar->method('getPath')->willReturn('/avatar.png');
        $user = $this->createMock(UserInfo::class);
        $user->method('getUserID')->willReturn($userID);
        $user->method('getUserName')->willReturn('jane');
        $user->method('getUserDateAdded')->willReturn('2026-01-02 03:04:05');
        $user->method('getUserAvatar')->willReturn($avatar);

        return $user;
    }
}
