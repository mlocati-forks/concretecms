<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="PageType model",
 * )
 */
class PageType implements \JsonSerializable
{
    /**
     * @OA\Property(title="Page Type Handle")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Page Type Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Handle of the page template that pages of this type get by default, empty when it has none")
     *
     * @var string
     */
    public $default_template;

    /**
     * @OA\Property(title="Handles of the page templates that pages of this type may use")
     *
     * @var string[]
     */
    public $templates;

    /**
     * @OA\Property(title="Handle of the package providing the page type, empty when it belongs to none")
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
