<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Attribute;

use Concrete\Core\Api\Attribute\Category\ApiHandler;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A set of attribute keys a client asks about, which a category of this installation serves.
 *
 * @see \Concrete\Core\Api\Attribute\Category\Express
 */
class Category
{
    /**
     * @var \Concrete\Core\Api\Attribute\Category\ApiHandler
     */
    private $handler;

    /**
     * @var string
     */
    private $handle;

    /**
     * @var string
     */
    private $package;

    /**
     * @var string
     */
    private $description;

    /**
     * @param \Concrete\Core\Api\Attribute\Category\ApiHandler $handler what the API does with the
     *                                                                  keys of the category serving this set, which built it
     * @param string $package the handle of the package that defined the category, empty for a
     *                        category of the core itself
     * @param string $description what the keys of this set belong to, which the handler says
     */
    public function __construct(ApiHandler $handler, string $handle, string $package = '', string $description = '')
    {
        $this->handler = $handler;
        $this->handle = $handle;
        $this->package = $package;
        $this->description = $description;
    }

    /**
     * Get how a client names this set of keys.
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * Get the handle of the package that defined the category these keys belong to.
     *
     * @return string an empty string for a category of the core itself
     */
    public function getPackageHandle(): string
    {
        return $this->package;
    }

    /**
     * Get what the keys of this set belong to, which tells a client where it writes them.
     *
     * @return string an empty string for a category that says nothing about itself
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get what the API does with the keys of this set.
     */
    public function getApiHandler(): ApiHandler
    {
        return $this->handler;
    }
}
