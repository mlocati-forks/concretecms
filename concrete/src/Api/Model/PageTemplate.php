<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="PageTemplate model",
 * )
 */
class PageTemplate
{
    /**
     * @OA\Property(type="string", title="Page Template Handle")
     *
     * @var string
     */
    private $handle;

    /**
     * @OA\Property(type="string", title="Page Template Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Handle of the package providing the page template, empty when it belongs to none")
     *
     * @var string
     */
    private $package;
}
