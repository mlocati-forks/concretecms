<?php

declare(strict_types=1);

namespace Concrete\Core\Api;

use Concrete\Core\Http\Request;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The scopes that the token of a request carries, which the middleware of the API takes off it and
 * lets the endpoints through by: they are read once, when this is built for the request, and a
 * request carrying no token carries no scope either.
 *
 * A thing of this installation travelling inside something else - a user named by an attribute of a
 * page, the author of an entry, the blocks of an area - is named and nothing else where the token
 * has no scope for the things of its kind, and the same goes where the permission that the endpoint
 * serving them asks about says no: the scopes settle one half of the question, which is the half
 * this answers. What names such a thing is what a write of it takes.
 *
 * Ask the container for it where it is needed instead of holding it: the middleware of the API drops
 * the one built before the token was read.
 *
 * @see \Concrete\Core\Http\Middleware\OAuthAuthenticationMiddleware::process()
 */
class TokenScopes
{
    /**
     * The scopes of the token, empty where the request carries none.
     *
     * @var string[]
     */
    protected $scopes;

    public function __construct(Request $request)
    {
        $this->scopes = [];
        foreach ((array) $request->attributes->get('oauth_scopes') as $scope) {
            if (is_string($scope) && $scope !== '') {
                $this->scopes[] = $scope;
            }
        }
    }

    /**
     * Whether the token of this request was given a scope.
     */
    public function has(string $scope): bool
    {
        return in_array($scope, $this->scopes, true);
    }

    /**
     * Whether the token of this request was given any of some scopes, which is what an endpoint asks
     * of it: one that names more than one is reached with any of them.
     *
     * @param string[] $scopes
     */
    public function hasAny(array $scopes): bool
    {
        foreach ($scopes as $scope) {
            if (is_string($scope) && $this->has($scope)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[] the scopes of the token, empty where the request carries none
     */
    public function getList(): array
    {
        return $this->scopes;
    }
}
