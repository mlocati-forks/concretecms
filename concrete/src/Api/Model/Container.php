<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="Container model",
 * )
 */
class Container
{
    /**
     * @OA\Property(type="string", title="Container Handle", description="What the container field of a core_container block takes")
     *
     * @var string
     */
    private $handle;

    /**
     * @OA\Property(type="string", title="Container Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Handle of the package providing the container, empty when it belongs to none")
     *
     * @var string
     */
    private $package;

    /**
     * @OA\Property(type="array", title="Handles of the page themes this container can be shown with", description="A page theme is listed when its files carry the template of the container, and every one of them is when the application directory carries it", @OA\Items(type="string"))
     *
     * @var string[]
     */
    private $page_themes;

    /**
     * @OA\Property(type="boolean", title="Whether the template of the container is in the application directory of this installation, which shows it with every page theme")
     *
     * @var bool
     */
    private $application;
}
