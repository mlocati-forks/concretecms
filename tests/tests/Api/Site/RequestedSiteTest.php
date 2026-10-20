<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Site;

use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Error\UserMessageHttpException;
use Concrete\Core\Http\Request;
use Concrete\Core\Permission\Response\SiteResponse;
use Concrete\Core\Site\Service as SiteService;
use Concrete\Core\User\User;
use Concrete\TestHelpers\Api\RequestedSiteController;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The site parameter of a request: what it names, and what an endpoint works with where it names
 * nothing.
 *
 * @see \Concrete\Core\Api\Traits\SupportsSiteTrait
 */
class RequestedSiteTest extends TestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Tests\TestCase::setUp()
     */
    public function setUp(): void
    {
        parent::setUp();
        // the permission response of a site is cached by the name of its class, which a mock shares
        // with the one of the test before
        app('cache/request')->flush();
        // with no permission key registered, only the superuser is allowed anything
        $superUser = $this->createMock(User::class);
        $superUser->method('isSuperUser')->willReturn(true);
        app()->instance(User::class, $superUser);
        // the permissions of a site are asked of the response of its own class, which the checker
        // builds through the container: answering here keeps the permission tables out of this test
        $this->allowReadingTheSites(true);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Tests\TestCase::tearDown()
     */
    public function tearDown(): void
    {
        app()->forgetInstance(User::class);
        app()->forgetInstance(SiteService::class);
        app()->forgetInstance(SiteResponse::class);
        parent::tearDown();
    }

    public function testARequestThatNamesNoSiteWorksWithTheOneOfItsOwnDomain(): void
    {
        $controller = $this->createController('');

        static::assertNull($controller->getNamedSite());
        static::assertSame(3, $controller->getSiteToWorkWith()->getSiteID());
    }

    public function testARequestNamingASiteWorksWithThatOne(): void
    {
        $controller = $this->createController('7');

        static::assertSame(7, $controller->getNamedSite()->getSiteID());
        static::assertSame(7, $controller->getSiteToWorkWith()->getSiteID());
    }

    /**
     * Zero names no site, the way a client that has nothing to say leaves the parameter out.
     */
    public function testZeroNamesNoSite(): void
    {
        static::assertNull($this->createController('0')->getNamedSite());
    }

    /**
     * @dataProvider provideParametersThatNameNoSiteOfThisInstallation
     */
    public function testAParameterNamingNoSiteOfThisInstallationIsRefused(string $parameter): void
    {
        $controller = $this->createController($parameter);

        try {
            $controller->getNamedSite();
        } catch (UserMessageHttpException $x) {
            static::assertSame(400, $x->getStatusCode());

            return;
        }
        static::fail("The site parameter {$parameter} names no site of this installation, and was taken all the same.");
    }

    /**
     * @return array<int,string[]>
     */
    public static function provideParametersThatNameNoSiteOfThisInstallation(): array
    {
        return [
            ['123'],
            ['-7'],
        ];
    }

    /**
     * A parameter that is no number at all names no site, the way leaving it out does.
     */
    public function testAParameterThatIsNoNumberNamesNoSite(): void
    {
        static::assertNull($this->createController('nonsense')->getNamedSite());
    }

    public function testASiteTheRequestMayNotReadIsRefused(): void
    {
        // what the site selector of the dashboard asks about, answered the way a private site answers
        $this->allowReadingTheSites(false);
        $controller = $this->createController('7');

        try {
            $controller->getNamedSite();
        } catch (UserMessageHttpException $x) {
            static::assertSame(403, $x->getStatusCode());

            return;
        }
        static::fail('A site that the request may not read was handed over all the same.');
    }

    public function testWhatBelongsToNoSiteBelongsToEveryOneOfThem(): void
    {
        $controller = $this->createController('');
        $site = $this->createSite(7);

        static::assertTrue($controller->isOfSite(null, $site));
        static::assertTrue($controller->isOfSite($this->createSite(7), $site));
        static::assertFalse($controller->isOfSite($this->createSite(8), $site));
    }

    private function allowReadingTheSites(bool $allowed): void
    {
        $response = $this->createMock(SiteResponse::class);
        $response->method('canViewSiteInSelector')->willReturn($allowed);
        app()->instance(SiteResponse::class, $response);
    }

    /**
     * @param string $parameter what the site parameter of the request says, empty to leave it out
     */
    private function createController(string $parameter): RequestedSiteController
    {
        $siteService = $this->createMock(SiteService::class);
        // the site that the domain the request came to answers for
        $siteService->method('getSite')->willReturn($this->createSite(3));
        $siteService->method('getByID')->willReturnCallback(function ($siteID) {
            return (int) $siteID === 7 ? $this->createSite(7) : null;
        });
        app()->instance(SiteService::class, $siteService);
        $query = $parameter === '' ? '' : '?site=' . rawurlencode($parameter);

        return app()->make(RequestedSiteController::class, [
            'request' => Request::create('/ccm/api/1.0/boards' . $query),
        ]);
    }

    private function createSite(int $siteID): Site
    {
        $site = $this->createMock(Site::class);
        $site->method('getSiteID')->willReturn($siteID);
        // a client reads the whole of a site it may view, and this one answers about its permissions
        $site->method('getPermissionResponseClassName')->willReturn(SiteResponse::class);
        $site->method('getPermissionObjectIdentifier')->willReturn((string) $siteID);

        return $site;
    }
}
