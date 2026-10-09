<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Attribute\Category;

use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Entity\Express\Entity;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The keys of the Express category belong to one entity each, so a client asks for them by entity.
 *
 * @method \Concrete\Core\Api\Attribute\Category\ApiHandler\Express getApiHandler()
 */
final class Express extends Category
{
    /**
     * What the handle of these sets of keys begins with, the ID of the entity following it.
     *
     * @var string
     */
    public const HANDLE_PREFIX = 'express@';

    /**
     * @var \Concrete\Core\Entity\Express\Entity
     */
    private $entity;

    public function __construct(ApiHandler\Express $handler, Entity $entity)
    {
        parent::__construct(
            $handler,
            self::HANDLE_PREFIX . $entity->getId(),
            '',
            t('Attributes of the entries of the %s entity', $entity->getName())
        );
        $this->entity = $entity;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }
}
