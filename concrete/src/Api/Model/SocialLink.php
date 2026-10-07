<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(title="SocialLink model", description="A social network a Concrete site is on.")
 */
class SocialLink implements \JsonSerializable
{
    /**
     * @OA\Property(format="int64", title="ID")
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(title="Handle of the social network")
     *
     * @var string
     */
    public $service_handle;

    /**
     * @OA\Property(title="Name of the social network, empty where this installation knows it no more")
     *
     * @var string
     */
    public $service_name;

    /**
     * @OA\Property(title="Address of the site on that social network")
     *
     * @var string
     */
    public $url;

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
