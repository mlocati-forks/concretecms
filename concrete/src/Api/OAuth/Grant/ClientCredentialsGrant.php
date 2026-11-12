<?php

declare(strict_types=1);

namespace Concrete\Core\Api\OAuth\Grant;

use League\OAuth2\Server\Grant\ClientCredentialsGrant as BaseClientCredentialsGrant;

defined('C5_EXECUTE') or die('Access Denied.');

class ClientCredentialsGrant extends BaseClientCredentialsGrant
{
    use ScopeValidationTrait;
}
