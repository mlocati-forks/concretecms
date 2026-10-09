<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey;

use Concrete\Core\Api\Model\AttributeKey;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeKeyTextarea",
 *     type="object",
 *     title="A key whose value is a text of more than one line",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeKey")}
 * )
 */
class Textarea extends AttributeKey
{
    /**
     * @OA\Property(
     *     title="What the key is meant to hold",
     *     description="A value of a rich text key is HTML, while the site shows the value of a text one as it is",
     *     enum={"text", "rich_text"}
     * )
     *
     * @var string
     */
    public $mode;
}
