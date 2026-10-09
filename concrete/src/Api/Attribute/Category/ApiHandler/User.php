<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Attribute\Category\ApiHandler;

use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Entity\Attribute\Key\Key;

defined('C5_EXECUTE') or die('Access Denied.');

class User extends ApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\Category\ApiHandler::getCategoryDescription()
     */
    public function getCategoryDescription(): string
    {
        return t('Attributes of users');
    }

    /**
     * {@inheritdoc}
     *
     * Where the site asks for an attribute of a user and where it shows it is up to this category,
     * whatever the type of the key.
     *
     * @param \Concrete\Core\Entity\Attribute\Key\UserKey $key
     *
     * @see \Concrete\Core\Api\Attribute\Category\ApiHandler::getApiKeyFields()
     */
    public function getApiKeyFields(Key $key): array
    {
        $user = new AttributeKeyModel\User();
        $user->editable_on_register = (bool) $key->isAttributeKeyEditableOnRegister();
        $user->required_on_register = (bool) $key->isAttributeKeyRequiredOnRegister();
        $user->editable_on_profile = (bool) $key->isAttributeKeyEditableOnProfile();
        $user->required_on_profile = (bool) $key->isAttributeKeyRequiredOnProfile();
        $user->displayed_on_profile = (bool) $key->isAttributeKeyDisplayedOnProfile();
        $user->displayed_on_member_list = (bool) $key->isAttributeKeyDisplayedOnMemberList();

        return ['user' => $user->jsonSerialize()];
    }
}
