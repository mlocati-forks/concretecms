<?php

declare(strict_types=1);

namespace Concrete\Core\Api\OAuth\Grant;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;

defined('C5_EXECUTE') or die('Access Denied.');

trait ScopeValidationTrait
{
    /**
     * {@inheritdoc}
     *
     * The base implementation stops at the first unknown scope, so a client asking for several
     * unknown scopes only discovers them one request at a time.
     */
    public function validateScopes($scopes, $redirectUri = null)
    {
        if ($scopes === null) {
            $scopes = [];
        } elseif (is_string($scopes)) {
            $scopes = array_filter(explode(self::SCOPE_DELIMITER_STRING, trim($scopes)), static function ($scope) {
                return $scope !== '';
            });
        }

        if (!is_array($scopes)) {
            throw OAuthServerException::invalidRequest('scope');
        }

        $validScopes = [];
        $unknownScopes = [];

        foreach ($scopes as $scopeItem) {
            $scope = $this->scopeRepository->getScopeEntityByIdentifier($scopeItem);

            if ($scope instanceof ScopeEntityInterface) {
                $validScopes[] = $scope;
            } else {
                $unknownScopes[] = $scopeItem;
            }
        }

        if ($unknownScopes !== []) {
            throw OAuthServerException::invalidScope(implode(self::SCOPE_DELIMITER_STRING, $unknownScopes), $redirectUri);
        }

        return $validScopes;
    }
}
