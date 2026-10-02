<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Api\Resources;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class SiteTransformer extends TransformerAbstract
{

    protected $defaultIncludes = [
        'locales',
    ];

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
            'defaults' => $this->getDefaults($site),
        ];
        // kept for the clients that came before the locales were handed over unasked
        $defaultLocale = $site->getDefaultLocale();
        $data['home_page_id'] = $site->getSiteHomePageID();
        $data['default_locale'] = $defaultLocale === null ? '' : (string) $defaultLocale->getLocale();

        return $data;
    }

    /**
     * Get what the pages of a site are given when they ask for nothing of their own.
     *
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

    /**
     * Get the page theme of a site (NULL when the theme it names is gone).
     */
    protected function getPageTheme(Site $site): ?PageTheme
    {
        return PageTheme::getByID($site->getThemeID());
    }

    public function includeLocales(Site $site)
    {
        $locales = [];
        foreach ($site->getLocales() as $locale) {
            if ($locale->getIsDefault()) {
                array_unshift($locales, $locale);
            } else {
                $locales[] = $locale;
            }
        }

        return new Collection($locales, new SiteLocaleTransformer(), Resources::RESOURCE_LOCALES);
    }

    public function includeCustomAttributes(Site $site)
    {
        $values = $site->getObjectAttributeCategory()->getAttributeValues($site);
        return new Collection($values, new AttributeValueTransformer(), Resources::RESOURCE_CUSTOM_ATTRIBUTES);
    }

}
