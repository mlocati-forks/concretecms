<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Guide;

use Concrete\Core\Api\Guide\PlaceholderFactory;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Site\Service;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @covers \Concrete\Core\Api\Guide\PlaceholderFactory
 */
class PlaceholderFactoryTest extends TestCase
{
    public function testThePlaceholdersTellWhereThisInstallationAnswers(): void
    {
        $factory = $this->createFactory([$this->createSite('Example', 'https://www.example.com')]);

        $placeholders = $factory->getPlaceholders();

        static::assertSame('Example', $placeholders['siteName']);
        static::assertSame('9.9.9', $placeholders['concreteVersion']);
        static::assertSame('https://www.example.com/index.php/', $placeholders['siteRootUrl']);
        static::assertSame('https://www.example.com/index.php/ccm/api/1.0', $placeholders['apiBaseUrl']);
        static::assertSame('https://www.example.com/index.php/ccm/api/1.0/system/openapi', $placeholders['openApiSpecificationUrl']);
    }

    public function testEverySiteIsListedWithItsNameAndItsUrl(): void
    {
        $factory = $this->createFactory([
            $this->createSite('Example', 'https://www.example.com'),
            $this->createSite('News: Archive', 'https://www.example.org'),
        ]);

        $expected = [
            '- Site name: Example<br/>',
            '  Site root URL: https://www.example.com/',
            '- Site name: News: Archive<br/>',
            '  Site root URL: https://www.example.org/',
        ];
        static::assertSame(implode("\n", $expected), $factory->getPlaceholders()['sites']);
    }

    public function testTheDefaultSiteIsListedWhereThisInstallationAnswers(): void
    {
        $factory = $this->createFactory([$this->createSite('Example', 'https://another-name.example.com', true)]);

        static::assertSame("- Site name: Example<br/>\n  Site root URL: https://www.example.com/index.php/", $factory->getPlaceholders()['sites']);
    }

    public function testASiteWithNoCanonicalUrlIsListedWithoutOne(): void
    {
        $factory = $this->createFactory([
            $this->createSite('Example', '', true),
            $this->createSite('Nowhere', ''),
        ]);

        static::assertStringEndsWith('- Site name: Nowhere', $factory->getPlaceholders()['sites']);
    }

    /**
     * @param \Concrete\Core\Entity\Site\Site[] $sites the sites of the installation, the first of
     *                                                 which is the one being served
     */
    private function createFactory(array $sites): PlaceholderFactory
    {
        $config = $this->createMock(Repository::class);
        $config->method('get')->with('concrete.version')->willReturn('9.9.9');
        $resolverManager = $this->createMock(ResolverManagerInterface::class);
        $resolverManager->method('resolve')->willReturnCallback(static function (array $arguments): string {
            // the real resolver prepends the dispatcher when URL rewriting is off
            return rtrim('https://www.example.com/index.php' . $arguments[0], '/');
        });
        $siteService = $this->createMock(Service::class);
        $siteService->method('getSite')->willReturn($sites[0]);
        $siteService->method('getList')->willReturn($sites);

        return new PlaceholderFactory($config, $resolverManager, $siteService);
    }

    private function createSite(string $name, string $canonicalUrl, bool $isDefault = false): Site
    {
        $site = $this->createMock(Site::class);
        $site->method('getSiteName')->willReturn($name);
        $site->method('getSiteCanonicalURL')->willReturn($canonicalUrl);
        $site->method('isDefault')->willReturn($isDefault);

        return $site;
    }
}
