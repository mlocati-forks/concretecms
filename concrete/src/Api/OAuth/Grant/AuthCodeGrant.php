<?php

declare(strict_types=1);

namespace Concrete\Core\Api\OAuth\Grant;

use League\OAuth2\Server\Grant\AuthCodeGrant as BaseAuthCodeGrant;

defined('C5_EXECUTE') or die('Access Denied.');

class AuthCodeGrant extends BaseAuthCodeGrant
{
    use ScopeValidationTrait;
}
