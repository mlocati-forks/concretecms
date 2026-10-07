<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Site;

use Concrete\Core\Api\Fractal\Transformer\SiteSocialLinkTransformer;
use Concrete\Core\Api\Fractal\Transformer\SiteTransformer;
use Concrete\Core\Entity\Sharing\SocialNetwork\Link;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Concrete\Core\Sharing\SocialNetwork\Service;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\DataArraySerializer;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A social_links block names a social network by its ID, so a client has to be able to read them.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\SiteTransformer::includeSocialLinks()
 */
class SocialLinksTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testASiteHandsOverTheSocialNetworksItIsOn(): void
    {
        $links = $this->transform([
            $this->createLink(7, 'facebook', 'Facebook', 'https://facebook.com/example'),
            $this->createLink(9, 'mastodon', 'Mastodon', 'https://example.social/@example'),
        ]);

        static::assertSame([
            [
                'id' => 7,
                'service_handle' => 'facebook',
                'service_name' => 'Facebook',
                'url' => 'https://facebook.com/example',
            ],
            [
                'id' => 9,
                'service_handle' => 'mastodon',
                'service_name' => 'Mastodon',
                'url' => 'https://example.social/@example',
            ],
        ], $links);
    }

    /**
     * A link of a network this installation knows no more keeps its address, which is all it has left.
     */
    public function testALinkToANetworkThatIsGoneIsStillHandedOver(): void
    {
        $links = $this->transform([$this->createLink(3, 'orkut', null, 'https://orkut.example.com/example')]);

        static::assertSame([[
            'id' => 3,
            'service_handle' => 'orkut',
            'service_name' => '',
            'url' => 'https://orkut.example.com/example',
        ]], $links);
    }

    public function testTheFieldsAreTheOnesTheSpecificationDescribes(): void
    {
        $link = $this->createLink(7, 'facebook', 'Facebook', 'https://facebook.com/example');

        $this->assertFieldsAre('SocialLink', (new SiteSocialLinkTransformer())->transform($link));
    }

    /**
     * @param \Concrete\Core\Entity\Sharing\SocialNetwork\Link[] $links
     *
     * @return array<int,array<string,mixed>> the social links of the site, as a client reads them
     */
    private function transform(array $links): array
    {
        $manager = new Manager();
        $manager->setSerializer(new DataArraySerializer());
        $transformer = new class ($links) extends SiteTransformer {
            /**
             * @var \Concrete\Core\Entity\Sharing\SocialNetwork\Link[]
             */
            private $links;

            /**
             * @param \Concrete\Core\Entity\Sharing\SocialNetwork\Link[] $links
             */
            public function __construct(array $links)
            {
                $this->links = $links;
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\SiteTransformer::getSocialLinks()
             */
            protected function getSocialLinks(Site $site): array
            {
                return $this->links;
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\SiteTransformer::getPageTheme()
             */
            protected function getPageTheme(Site $site): ?PageTheme
            {
                return null;
            }
        };
        $transformer->setDefaultIncludes(['social_links']);
        $data = $manager->createData(new Item($this->createSite(), $transformer))->toArray();

        return $data['data']['social_links']['data'];
    }

    /**
     * @param string|null $serviceName the name of the network, NULL where this installation knows it no more
     */
    private function createLink(int $id, string $serviceHandle, ?string $serviceName, string $url): Link
    {
        $service = null;
        if ($serviceName !== null) {
            $service = $this->createMock(Service::class);
            $service->method('getDisplayName')->willReturn($serviceName);
        }
        $link = $this->createMock(Link::class);
        $link->method('getID')->willReturn($id);
        $link->method('getServiceHandle')->willReturn($serviceHandle);
        $link->method('getServiceObject')->willReturn($service);
        $link->method('getURL')->willReturn($url);

        return $link;
    }

    private function createSite(): Site
    {
        $site = $this->createMock(Site::class);
        $site->method('getSiteID')->willReturn(1);
        $site->method('getSiteHandle')->willReturn('default');
        $site->method('getSiteName')->willReturn('Example');

        return $site;
    }
}
