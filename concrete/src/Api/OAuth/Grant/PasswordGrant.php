<?php

declare(strict_types=1);

namespace Concrete\Core\Api\OAuth\Grant;

use League\OAuth2\Server\Grant\PasswordGrant as BasePasswordGrant;

defined('C5_EXECUTE') or die('Access Denied.');

class PasswordGrant extends BasePasswordGrant
{
    use ScopeValidationTrait;
}
