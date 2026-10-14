<?php

declare(strict_types=1);

namespace Concrete\Tests\Api;

use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Http\Request;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\TokenScopes
 */
class TokenScopesTest extends TestCase
{
    public function testTheScopesOfTheTokenAreTheOnesTheMiddlewareTookOffIt(): void
    {
        $scopes = $this->createScopes(['pages:read', 'files:read']);

        static::assertSame(['pages:read', 'files:read'], $scopes->getList());
        static::assertTrue($scopes->has('files:read'));
        static::assertFalse($scopes->has('users:read'));
    }

    public function testARequestCarryingNoTokenHasNoScope(): void
    {
        $scopes = $this->createScopes(null);

        static::assertSame([], $scopes->getList());
        static::assertFalse($scopes->has('pages:read'));
    }

    public function testARequestCarryingNoAttributeIsOneCarryingNoScope(): void
    {
        static::assertSame([], $this->createScopes(null)->getList());
        static::assertSame([], $this->createScopes([])->getList());
    }

    /**
     * @param string[]|null $scopes what the middleware left on the request
     */
    private function createScopes(?array $scopes): TokenScopes
    {
        $request = Request::create('/ccm/api/1.0/pages/1');
        if ($scopes !== null) {
            $request->attributes->set('oauth_scopes', $scopes);
        }

        return new TokenScopes($request);
    }
}
