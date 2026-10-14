<?php

declare(strict_types=1);

namespace Concrete\TestHelpers\Api;

use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Http\Request;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A test reading a thing of this installation says with which scopes it is reading it, the way a
 * request of the API carries them: without them a thing travels named and nothing else.
 *
 * @see \Concrete\Core\Api\TokenScopes
 */
trait TokenScopesTrait
{
    /**
     * @param string[] $scopes the scopes of the token the request carries
     */
    protected function giveTheTokenScopes(array $scopes): void
    {
        $request = Request::create('/ccm/api/1.0/');
        $request->attributes->set('oauth_scopes', $scopes);
        app()->instance(TokenScopes::class, new TokenScopes($request));
    }

    protected function takeTheTokenBack(): void
    {
        app()->forgetInstance(TokenScopes::class);
    }
}
