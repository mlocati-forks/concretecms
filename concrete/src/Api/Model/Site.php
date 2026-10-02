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
     * @OA\Property(type="integer", title="Home Page ID")
     *
     * @var string
     */
    private $home_page_id;

    /**
     * @OA\Property(type="string", title="Default Locale")
     *
     * @var string
     */
    private $default_locale;

    /**
     * @OA\Property(ref="#/components/schemas/SiteDefaults", title="What the pages of this site are given when they ask for nothing of their own")
     *
     * @var \Concrete\Core\Api\Model\SiteDefaults
     */
    private $defaults;

    /**
     * @OA\Property(type="array", title="Locales", @OA\Items(ref="#/components/schemas/Locale"))
     *
     * @var string
     */
    private $locales;





}
