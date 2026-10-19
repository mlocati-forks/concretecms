<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(title="Locale", description="A Concrete Site Locale object.")
 */
class Locale
{

    /**
     * @OA\Property(type="integer", title="ID")
     *
     * @var string
     */
    private $id;

    /**
     * @OA\Property(type="string", title="Locale")
     *
     * @var string
     */
    private $locale;

    /**
     * @OA\Property(type="string", title="Language of the locale")
     *
     * @var string
     */
    private $language;

    /**
     * @OA\Property(type="string", title="Country", description="Country of the locale, empty when it speaks of none")
     *
     * @var string
     */
    private $country;

    /**
     * @OA\Property(type="string", title="Path", description="Path the pages of the locale hang from. The default locale hangs from /, the others from a path of their own")
     *
     * @var string
     */
    private $path;

    /**
     * @OA\Property(type="integer", title="Home page ID", description="ID of the page the locale hangs from. The pages of a locale are the ones below it, so a page is written in the locale of the page it hangs from")
     *
     * @var int
     */
    private $home_page_id;

    /**
     * @OA\Property(type="boolean", title="Default locale?", description="Whether this is the default locale of the site")
     *
     * @var bool
     */
    private $is_default;




}
