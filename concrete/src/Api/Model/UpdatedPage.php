<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="UpdatedPage model",
 *     description="A Concrete Page"
*     )
 */
class UpdatedPage
{

    /**
     * @OA\Property(type="string", title="ID")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Last part of the path of the page", description="It is made out of the name of the page when it is missing")
     *
     * @var string
     */
    private $url_slug;

    /**
     * @OA\Property(type="string", title="Short description")
     *
     * @var string
     */
    private $description;

    /**
     * @OA\Property(type="string", title="Page Type", description="The handle of the page type you want to apply to this page.")
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(type="string", title="Page Type", description="The handle of the page template you want to apply to this page.")
     *
     * @var string
     */
    private $template;

    /**
     * @OA\Property(type="string", title="Page Theme", description="The handle of the page theme you want this page to be shown with")
     *
     * @var string
     */
    private $theme;

    /**
     * @OA\Property(type="string", title="Page Theme Skin", description="The identifier of the skin of that theme you want this page to be shown with, empty to let this page use the skin of its site")
     *
     * @var string
     */
    private $theme_skin;


}
