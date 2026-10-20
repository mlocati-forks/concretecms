<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(title="Page", description="A Concrete Page object.")
 */
class Page
{

    /**
     * @OA\Property(type="integer", title="ID")
     *
     * @var string
     */
    private $id;

    /**
     * @OA\Property(type="string", title="Page path")
     *
     * @var string
     */
    private $path;

    /**
     * @OA\Property(type="string", title="URL slug", description="Last part of the path of the page")
     *
     * @var string
     */
    private $url_slug;

    /**
     * @OA\Property(type="string", title="Page Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Page Type")
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(type="string", title="Page Template")
     *
     * @var string
     */
    private $template;

    /**
     * @OA\Property(type="string", title="Page theme", description="Handle of the page theme the page is shown with")
     *
     * @var string
     */
    private $theme;

    /**
     * @OA\Property(type="string", title="Theme skin", description="Identifier of the skin of that theme the page is shown with")
     *
     * @var string
     */
    private $theme_skin;

    /**
     * @OA\Property(type="date", title="Date page created")
     *
     * @var string
     */
    private $date_added;

    /**
     * @OA\Property(type="date", title="Date page last updated")
     *
     * @var string
     */
    private $date_last_updated;

    /**
     * @OA\Property(type="date", title="Date page made public")
     *
     * @var string
     */
    private $date_public;

    /**
     * @OA\Property(type="date", title="Locale of the page", description="Locale of the page - defaults to site if unset.")
     *
     * @var string
     */
    private $locale;

    /**
     * @OA\Property(
     *     type="integer",
     *     format="int64",
     *     nullable=true,
     *     title="Site ID",
     *     description="ID of the site the page belongs to, NULL for a page this installation keeps outside of its sites, as the ones holding the stacks"
     * )
     *
     * @var int|null
     */
    private $site_id;

    /**
     * @OA\Property(type="string", title="External Link URL", description="If this page node is actually an external link, the value of the link URL.")
     *
     * @var string
     */
    private $external_link_url;

    /**
     * @OA\Property(type="string", title="Short description")
     *
     * @var string
     */
    private $description;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Custom attributes",
     *     description="Custom attributes of the page, where the includes parameter asks for them",
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

    /**
     * @OA\Property(
     *     type="object",
     *     title="Areas",
     *     description="Areas of the page, where the includes parameter asks for them",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/Area")
     *     )
     * )
     *
     * @var array
     */
    private $areas;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Files",
     *     description="Files the page holds, where the includes parameter asks for them",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/File")
     *     )
     * )
     *
     * @var array
     */
    private $files;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Content",
     *     description="Content of the page, where the includes parameter asks for it",
     *     @OA\Property(
     *         property="data",
     *         ref="#/components/schemas/Content"
     *     )
     * )
     *
     * @var array
     */
    private $content;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Version",
     *     description="Version of the page this answer is about, which every answer carries",
     *     @OA\Property(
     *         property="data",
     *         ref="#/components/schemas/PageVersion"
     *     )
     * )
     *
     * @var array
     */
    private $version;



}
