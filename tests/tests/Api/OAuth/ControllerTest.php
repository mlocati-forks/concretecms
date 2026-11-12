<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\OAuth;

use Concrete\Core\Api\OAuth\Controller;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\OAuth\Client;
use Concrete\Core\Entity\OAuth\Scope;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Tests\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Mockery as M;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * An integration can be limited to some scopes: asking for the other ones used to end in a generic
 * 500 error that did not even name the rejected scopes.
 *
 * The scopes in these tests always exist: only the ones granted to the integration differ.
 *
 * @covers \Concrete\Core\Api\OAuth\Controller
 */
class ControllerTest extends TestCase
{
    public const CLIENT_KEY = 'client-key';

    public const REDIRECT_URI = 'https://client.example.com/callback';

    /**
     * @param string $identifier
     *
     * @return \Concrete\Core\Entity\OAuth\Scope
     */
    protected function scope($identifier)
    {
        $scope = new Scope();
        $scope->setIdentifier($identifier);

        return $scope;
    }

    /**
     * Ask for some scopes the way the authorize endpoint would, with a client limited to some scopes.
     *
     * @param string[] $allowedScopes
     * @param string[] $requestedScopes
     * @param string|null $requestedRedirectUri
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function authorize(array $allowedScopes, array $requestedScopes, $requestedRedirectUri = self::REDIRECT_URI)
    {
        $client = new Client();
        $client->setIdentifier('client-uuid');
        $client->setClientKey(self::CLIENT_KEY);
        $client->setRedirectUri(self::REDIRECT_URI);
        $client->setHasCustomScopes(true);
        $client->setScopes(new ArrayCollection(array_map([$this, 'scope'], $allowedScopes)));

        $authorizationRequest = new AuthorizationRequest();
        $authorizationRequest->setClient($client);
        $authorizationRequest->setGrantTypeId('authorization_code');
        $authorizationRequest->setScopes(array_map([$this, 'scope'], $requestedScopes));
        if ($requestedRedirectUri !== null) {
            $authorizationRequest->setRedirectUri($requestedRedirectUri);
        }

        $oauthServer = M::mock(AuthorizationServer::class);
        $oauthServer->shouldReceive('validateAuthorizationRequest')->once()->andReturn($authorizationRequest);

        $request = (new ServerRequest('GET', 'https://example.com/oauth/2.0/authorize'))
            ->withQueryParams(['client_id' => self::CLIENT_KEY])
        ;

        $controller = new Controller(
            $oauthServer,
            M::mock(EntityManagerInterface::class),
            $request,
            new Session(new MockArraySessionStorage()),
            M::mock(Token::class),
            M::mock(Repository::class)
        );

        return $controller->authorize();
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array the error details sent back to the client
     */
    protected function assertRedirectedWithError($response)
    {
        static::assertSame(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        static::assertStringStartsWith(self::REDIRECT_URI . '?', $location);
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        return $query;
    }

    public function testScopesNotAllowedForTheClientAreAllReported()
    {
        $response = $this->authorize(['account:read'], ['users:delete', 'account:read', 'users:add']);

        $error = $this->assertRedirectedWithError($response);
        static::assertSame('invalid_scope', $error['error']);
        static::assertSame('The requested scope is not allowed for this client', $error['error_description']);
        static::assertSame('Check the `users:delete users:add` scope', $error['hint']);
    }

    /**
     * The redirect URI is optional when the client has just one.
     */
    public function testErrorIsSentToTheClientRedirectUriWhenTheRequestHasNone()
    {
        $response = $this->authorize(['account:read'], ['users:delete'], null);

        $error = $this->assertRedirectedWithError($response);
        static::assertSame('invalid_scope', $error['error']);
        static::assertSame('Check the `users:delete` scope', $error['hint']);
    }
}
