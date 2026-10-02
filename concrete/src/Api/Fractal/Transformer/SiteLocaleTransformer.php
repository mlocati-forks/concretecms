<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Entity\Site\Locale;
use Concrete\Core\Page\Page;
use League\Fractal\TransformerAbstract;

class SiteLocaleTransformer extends TransformerAbstract
{

    public function transform(Locale $locale)
    {
        $homePageID = null;
        $homePage = null;
        $tree = $locale->getSiteTreeObject();
        if ($tree) {
            $homePageID = $tree->getSiteHomePageID();
            $homePage = $tree->getSiteHomePageObject();
        }
        return [
            'id' => $locale->getLocaleID(),
            'locale' => $locale->getLocale(),
            'language' => $locale->getLanguage(),
            'country' => $locale->getCountry(),
            'path' => $this->getPath($homePage),
            'home_page_id' => $homePageID,
            'is_default' => (bool) $locale->getIsDefault(),
        ];
    }

    /**
     * @param \Concrete\Core\Page\Page|null $homePage the page the locale hangs from
     * @return string an empty string when the locale hangs from no page of the site
     */
    protected function getPath(?Page $homePage): string
    {
        if ($homePage === null) {
            return '';
        }

        return '/' . trim((string) $homePage->getCollectionPath(), '/');
    }

}
