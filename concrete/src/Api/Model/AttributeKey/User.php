<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * What the user category adds to one of its keys, whatever the type of the key: where the site asks
 * for the attribute, and where it shows it.
 *
 * @OA\Schema(
 *     schema="AttributeKeyUser",
 *     type="object",
 *     title="What the user category adds to one of its keys",
 * )
 */
class User implements \JsonSerializable
{
    /**
     * @OA\Property(title="Whether the registration form of the site asks for this attribute")
     *
     * @var bool
     */
    public $editable_on_register;

    /**
     * @OA\Property(title="Whether the registration form of the site insists on this attribute")
     *
     * @var bool
     */
    public $required_on_register;

    /**
     * @OA\Property(title="Whether a user may write this attribute of their own profile")
     *
     * @var bool
     */
    public $editable_on_profile;

    /**
     * @OA\Property(title="Whether the profile form of the site insists on this attribute")
     *
     * @var bool
     */
    public $required_on_profile;

    /**
     * @OA\Property(title="Whether the public profile of a user shows this attribute")
     *
     * @var bool
     */
    public $displayed_on_profile;

    /**
     * @OA\Property(title="Whether the member list of the site shows this attribute")
     *
     * @var bool
     */
    public $displayed_on_member_list;

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
