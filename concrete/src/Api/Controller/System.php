<?php

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Guide\GuideGenerator;
use Concrete\Core\Api\OpenApi\SpecGenerator;
use Concrete\Core\Http\Request;
use Concrete\Core\System\Info;
use Concrete\Core\System\InfoTransformer;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpFoundation\Response;

class System extends ApiController
{

    /**
     * @var \Concrete\Core\Api\Guide\GuideGenerator
     */
    protected $guideGenerator;

    /**
     * @var \Concrete\Core\Api\OpenApi\SpecGenerator
     */
    protected $specGenerator;

    public function __construct(Request $request, GuideGenerator $guideGenerator, SpecGenerator $specGenerator)
    {
        parent::__construct($request);
        $this->guideGenerator = $guideGenerator;
        $this->specGenerator = $specGenerator;
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/system/info",
     *     tags={"system"},
     *     operationId="getSystemInfo",
     *     summary="Describe this installation: the version it runs, the packages it holds, the PHP it is served by",
     *     security={
     *         {"clientCredentials": {"system:info:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SystemInfo")
     *         ),
     *     ),
     * )
     */
    public function info()
    {
        return new Item(new Info(), new InfoTransformer());
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/system/openapi",
     *     tags={"system"},
     *     operationId="getOpenApiSpecification",
     *     summary="Get the OpenAPI specification of this installation",
     *     security={
     *         {"clientCredentials": {"system:openapi:read"}},
     *         {"authorization": {"system:openapi:read"}}
     *     },
     *     @OA\Parameter(
     *         name="format",
     *         in="query",
     *         description="The format of the specification (default: json)",
     *         @OA\Schema(
     *             type="string",
     *             enum={"json", "yaml"}
     *         )
     *     ),
     *     @OA\Response(response="200", description="The OpenAPI specification of this installation")
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function openapi()
    {
        $spec = $this->specGenerator->getSpec();
        if (strtolower((string) $this->request->query->get('format', 'json')) === 'yaml') {
            return new Response($spec->toYaml(), Response::HTTP_OK, ['Content-Type' => 'application/yaml']);
        }

        return new Response($spec->toJson(), Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/system/guide",
     *     tags={"system"},
     *     operationId="getSystemGuide",
     *     summary="Get the guide that tells how this installation expects its API to be used",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Response(
     *         response="200",
     *         description="The guide of the API, in markdown format",
     *         @OA\MediaType(
     *             mediaType="text/markdown",
     *             @OA\Schema(type="string")
     *         )
     *     )
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function guide()
    {
        return new Response(
            $this->guideGenerator->getGuide(),
            Response::HTTP_OK,
            ['Content-Type' => 'text/markdown; charset=' . APP_CHARSET]
        );
    }

}
