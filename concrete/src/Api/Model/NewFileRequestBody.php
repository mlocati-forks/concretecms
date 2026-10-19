<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\RequestBody(
 *     request="NewFile",
 *     description="Adding a file to the CMS in one of these ways, and one at a time: upload it as a multipart/form-data part, or ask for it with a JSON body that carries its contents base64-encoded, an URL for Concrete to download, or the name of a file of the incoming directory of this site",
 *     required=true,
 *     @OA\MediaType(
 *         mediaType="multipart/form-data",
 *         @OA\Schema(ref="#/components/schemas/NewFile")
 *     ),
 *     @OA\MediaType(
 *         mediaType="application/json",
 *         @OA\Schema(ref="#/components/schemas/NewFileFromJson")
 *     ),
 * )
 */
class NewFileRequestBody
{



}
