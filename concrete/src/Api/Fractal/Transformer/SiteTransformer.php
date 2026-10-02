<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Api\Resources;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class SiteTransformer extends TransformerAbstract
{

    protected $availableIncludes = [
        'locales',
        'custom_attributes',
    ];

    public function transform(Site $site)
    {
        $data = [
            'id' => $site->getSiteID(),
            'handle' => $site->getSiteHandle(),
            'name' => $site->getSiteName(),
            'home_page_id' => $site->getSiteHomePageID(),
            'defaults' => $this->getDefaults($site),
        ];
        $defaultLocale = $site->getDefaultLocale();
        $data['default_locale'] = $defaultLocale === null ? '' : (string) $defaultLocale->getLocale();

        return $data;
    }

    /**
     * @return array<string,mixed>
     */
    public function getDefaults(Site $site): array
    {
        $theme = $this->getPageTheme($site);

        return [
            'page_theme' => $theme === null ? '' : (string) $theme->getThemeHandle(),
            'page_theme_skin' => (string) $site->getThemeSkinIdentifier(),
            'page_theme_skin_dark' => (string) $site->getThemeSkinIdentifierDark(),
        ];
    }

    protected function getPageTheme(Site $site): ?PageTheme
    {
        return PageTheme::getByID($site->getThemeID());
    }

    public function includeLocales(Site $site)
    {
        $locales = $site->getLocales();

        return new Collection($locales, new SiteLocaleTransformer(), Resources::RESOURCE_LOCALES);
    }

    public function includeCustomAttributes(Site $site)
    {
        $values = $site->getObjectAttributeCategory()->getAttributeValues($site);
        return new Collection($values, new AttributeValueTransformer(), Resources::RESOURCE_CUSTOM_ATTRIBUTES);
    }

}
