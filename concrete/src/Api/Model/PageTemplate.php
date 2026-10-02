<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="PageTemplate model",
 * )
 */
class PageTemplate implements \JsonSerializable
{
    /**
     * @OA\Property(title="Page Template Handle")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Page Template Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Handle of the package providing the page template, empty when it belongs to none")
     *
     * @var string
     */
    public $package;

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
