<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(title="Site model", description="A Concrete Site object.")
 */
class Site
{

    /**
     * @OA\Property(type="integer", title="ID")
     *
     * @var string
     */
    private $id;

    /**
     * @OA\Property(type="string", title="Site handle")
     *
     * @var string
     */
    private $handle;

    /**
     * @OA\Property(type="string", title="Site Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(ref="#/components/schemas/SiteDefaults", title="What the pages of this site are given when they ask for nothing of their own")
     *
     * @var \Concrete\Core\Api\Model\SiteDefaults
     */
    private $defaults;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Locales of the site, the default one coming first, which every answer carries",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/Locale")
     *     )
     * )
     *
     * @var array
     */
    private $locales;

    /**
     * @OA\Property(type="integer", title="Home Page ID", deprecated=true, description="Use the home_page_id of the default locale instead")
     *
     * @var string
     */
    private $home_page_id;

    /**
     * @OA\Property(type="string", title="Default Locale", deprecated=true, description="Use the locale field of the default locale instead")
     *
     * @var string
     */
    private $default_locale;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Custom attributes of the site, which the answer about a single site carries",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/CustomAttribute")
     *     )
     * )
     *
     * @var array
     */
    private $custom_attributes;
}
