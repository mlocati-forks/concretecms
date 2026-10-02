<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="PageType model",
 * )
 */
class PageType
{
    /**
     * @OA\Property(type="string", title="Page Type Handle")
     *
     * @var string
     */
    private $handle;

    /**
     * @OA\Property(type="string", title="Page Type Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Handle of the page template that pages of this type get by default, empty when it has none")
     *
     * @var string
     */
    private $default_template;

    /**
     * @OA\Property(type="array", title="Handles of the page templates that pages of this type may use", @OA\Items(type="string"))
     *
     * @var string[]
     */
    private $templates;

    /**
     * @OA\Property(type="string", title="Handle of the package providing the page type, empty when it belongs to none")
     *
     * @var string
     */
    private $package;
}
