<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\OAuth\Grant;

use Concrete\Core\Api\OAuth\Grant\AuthCodeGrant;
use Concrete\Core\Api\OAuth\Grant\ClientCredentialsGrant;
use Concrete\Core\Api\OAuth\Grant\PasswordGrant;
use Concrete\Core\Api\OAuth\Grant\RefreshTokenGrant;
use Concrete\Core\Api\OAuth\Grant\ScopeValidationTrait;
use Concrete\Core\Entity\OAuth\Scope;
use Concrete\Tests\TestCase;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Mockery as M;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The stock League grants report only the first unknown scope, so a client asking for several unknown
 * scopes has to repeat the request once per scope to discover them all.
 *
 * @covers \Concrete\Core\Api\OAuth\Grant\ScopeValidationTrait
 */
class ScopeValidationTraitTest extends TestCase
{
    public const KNOWN_SCOPES = ['account:read', 'pages:read'];

    /**
     * @var \Concrete\Core\Api\OAuth\Grant\ClientCredentialsGrant
     */
    protected $grant;

    public function setUp(): void
    {
        parent::setUp();

        $scopeRepository = M::mock(ScopeRepositoryInterface::class);
        $scopeRepository->shouldReceive('getScopeEntityByIdentifier')->andReturnUsing(static function ($identifier) {
            if (!in_array($identifier, self::KNOWN_SCOPES, true)) {
                return;
            }
            $scope = new Scope();
            $scope->setIdentifier($identifier);

            return $scope;
        });

        $this->grant = new ClientCredentialsGrant();
        $this->grant->setScopeRepository($scopeRepository);
    }

    /**
     * @param \League\OAuth2\Server\Entities\ScopeEntityInterface[] $scopes
     *
     * @return string[]
     */
    protected function identifiersOf(array $scopes)
    {
        $result = [];
        foreach ($scopes as $scope) {
            $result[] = $scope->getIdentifier();
        }

        return $result;
    }

    public function testKnownScopesAreAccepted()
    {
        static::assertSame(self::KNOWN_SCOPES, $this->identifiersOf($this->grant->validateScopes('account:read pages:read')));
        static::assertSame(self::KNOWN_SCOPES, $this->identifiersOf($this->grant->validateScopes(self::KNOWN_SCOPES)));
        static::assertSame(['pages:read'], $this->identifiersOf($this->grant->validateScopes('  pages:read  ')));
    }

    public function testNoScopesAreAccepted()
    {
        static::assertSame([], $this->grant->validateScopes(null));
        static::assertSame([], $this->grant->validateScopes(''));
    }

    /**
     * With just one unknown scope the error must be the one of the stock League grants.
     */
    public function testSingleUnknownScopeIsReported()
    {
        try {
            $this->grant->validateScopes('account:read unknown');
            static::fail('An unknown scope was accepted.');
        } catch (OAuthServerException $e) {
            static::assertSame('invalid_scope', $e->getErrorType());
            static::assertSame('Check the `unknown` scope', $e->getHint());
        }
    }

    public function testAllUnknownScopesAreReported()
    {
        try {
            $this->grant->validateScopes('unknown-1 account:read unknown-2');
            static::fail('Unknown scopes were accepted.');
        } catch (OAuthServerException $e) {
            static::assertSame('invalid_scope', $e->getErrorType());
            static::assertSame('Check the `unknown-1 unknown-2` scope', $e->getHint());
        }
    }

    public function testInvalidScopeListIsRejected()
    {
        try {
            $this->grant->validateScopes(123);
            static::fail('A scope list that is neither a string nor an array was accepted.');
        } catch (OAuthServerException $e) {
            static::assertSame('invalid_request', $e->getErrorType());
        }
    }

    public function provideGrantClasses()
    {
        return [
            [AuthCodeGrant::class],
            [ClientCredentialsGrant::class],
            [PasswordGrant::class],
            [RefreshTokenGrant::class],
        ];
    }

    /**
     * @dataProvider provideGrantClasses
     *
     * @param string $grantClass
     */
    public function testEveryGrantReportsAllUnknownScopes($grantClass)
    {
        static::assertContains(ScopeValidationTrait::class, class_uses($grantClass));
    }
}
