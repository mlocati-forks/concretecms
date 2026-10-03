<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="Container model",
 * )
 */
class Container implements \JsonSerializable
{
    /**
     * @OA\Property(title="Container Handle", description="What the container field of a core_container block takes")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Container Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Handle of the package providing the container, empty when it belongs to none")
     *
     * @var string
     */
    public $package;

    /**
     * @OA\Property(title="Handles of the page themes this container can be shown with", description="A page theme is listed when its files carry the template of the container, and every one of them is when the application directory carries it")
     *
     * @var string[]
     */
    public $page_themes;

    /**
     * @OA\Property(title="Whether the template of the container is in the application directory of this installation, which shows it with every page theme")
     *
     * @var bool
     */
    public $application;

    /**
     * {@inheritdoc}
     *
     * @see \JsonSerializable::jsonSerialize()
     *
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}
