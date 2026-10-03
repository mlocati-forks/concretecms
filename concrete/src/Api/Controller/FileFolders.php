<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\FileFolderTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Api\Tree\TreeNodes;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Tree\Type\FileManager as FileManagerTree;

defined('C5_EXECUTE') or die('Access Denied.');

class FileFolders extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/file_folders",
     *     tags={"file_folders"},
     *     operationId="getFileFolders",
     *     summary="Answer with the root of the file manager, the folder every file of this site is filed in, with the folders it holds",
     *     security={
     *         {"authorization": {"files:read"}}
     *     },
     *     @OA\Parameter(
     *         name="path",
     *         in="query",
     *         description="The path of the folder to answer with instead of the root: the names are matched whatever their case, and the first folder of a name wins where two of them differ by case alone",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FileFolderDetail"),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="The request may not view the folder asked for",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No folder of this site has the path asked for",
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listFileFolders()
    {
        $tree = FileManagerTree::get();
        if ($tree === null) {
            return $this->error(t('The file manager has no folders.'), 404);
        }
        if (!$this->request->query->has('path')) {
            return $this->answerWithFolder($tree->getRootTreeNodeObject());
        }
        // the core walks a path by the names of the folders, taking the first one of a name
        $folder = $tree->getNodeByDisplayPath((string) $this->request->query->get('path'));

        return $this->answerWithFolder($folder instanceof Node ? $folder : null);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/file_folders/{fileFolderID}",
     *     tags={"file_folders"},
     *     operationId="getFileFolderById",
     *     summary="Find a folder of files by its ID, with the folders it holds",
     *     security={
     *         {"authorization": {"files:read"}}
     *     },
     *     @OA\Parameter(
     *         name="fileFolderID",
     *         in="path",
     *         description="ID of the folder to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FileFolderDetail"),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="The request may not view this folder",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No folder of this site has this ID",
     *     ),
     * )
     *
     * @param int|string $fileFolderID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($fileFolderID)
    {
        return $this->answerWithFolder(Node::getByID((int) $fileFolderID));
    }

    /**
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    private function answerWithFolder(?Node $folder)
    {
        $treeNodes = $this->app->make(TreeNodes::class);
        if ($folder === null || $folder->getTreeNodeTypeHandle() !== FileFolderTransformer::NODE_TYPE_FOLDER) {
            return $this->error(t('Folder not found.'), 404);
        }
        if (!$treeNodes->canView($folder)) {
            return $this->error(t('You do not have access to read properties about this folder.'), 401);
        }

        return $this->transform($folder, new FileFolderTransformer($treeNodes, true), Resources::RESOURCE_FILE_FOLDERS);
    }
}
