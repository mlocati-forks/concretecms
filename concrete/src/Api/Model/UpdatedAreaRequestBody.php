<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\RequestBody(
 *     request="UpdatedArea",
 *     description="Area of a page updated through the CMS",
 *     required=true,
 *     @OA\JsonContent(ref="#/components/schemas/UpdatedArea"),
 * )
 */
class UpdatedAreaRequestBody
{
}
