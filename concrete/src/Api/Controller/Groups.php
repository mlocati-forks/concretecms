<?php

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Permission\Checker;
use Concrete\Core\Search\Pagination\PagerPagination;
use Concrete\Core\Tree\Node\Node as TreeNode;
use Concrete\Core\User\Group\Command\AddGroupCommand;
use Concrete\Core\User\Group\FolderManager;
use Concrete\Core\User\Group\Group;
use Concrete\Core\User\Group\GroupList;
use Concrete\Core\Api\ApiController;
use Concrete\Core\User\Group\Search\ColumnSet\Column\GroupIdColumn;
use Concrete\Core\Api\Cursor\IntegerCursor;
use Concrete\Core\Api\Fractal\Transformer\GroupTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Api\Traits\SetListLimitFromQueryTrait;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class Groups extends ApiController
{

    use SetListLimitFromQueryTrait;

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/groups/{groupID}",
     *     tags={"groups"},
     *     operationId="getGroupById",
     *     summary="Find a user group by its ID",
     *     security={
     *         {"authorization": {"groups:read"}}
     *     },
     *     @OA\Parameter(
     *         name="groupID",
     *         in="path",
     *         description="ID of group",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful groups operation",
     *         @OA\JsonContent(ref="#/components/schemas/Group"),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You do not have the proper permissions to access this resource."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Group not found"
     *     ),
     * )
     * )
     */
    public function read($gID)
    {
        $fID = (int) $gID;
        $group = Group::getByID($fID);
        if (!$group) {
            return $this->error(t('Group not found.'), 404);
        } else {
            $permissions = new Checker($group);
            if (!$permissions->canViewTreeNode()) {
                return $this->error(t('You do not have access to get information about this group.'), 403);
            }
        }

        return $this->transform($group, new GroupTransformer(), Resources::RESOURCE_GROUPS);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/groups",
     *     tags={"groups"},
     *     operationId="getGroups",
     *     summary="Returns a list of Group objects, sorted by ID ascending.",
     *     security={
     *         {"authorization": {"groups:read"}}
     *     },
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="The number of objects to return. Must be 100 or less. Defaults to 10.",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="after",
     *         in="query",
     *         description="The ID of the current object to start after.",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Group")
     *         ),
     *     ),
     * )
     */
    public function listGroups()
    {
        $list = new GroupList();
        $list->setPermissionsChecker(function($group) {
            $permissions = new Checker($group);
            return $permissions->canViewTreeNode();
        });
        $groupIdColumn = new GroupIdColumn();
        $list->sortBySearchColumn($groupIdColumn);
        $cursor = new IntegerCursor(static function ($group) {
            return (int) $group->getGroupID();
        });
        $cursor->startAfter($this->request, $list, $groupIdColumn, static function ($currentCursor) {
            return Group::getByID($currentCursor);
        });

        $pagination = new PagerPagination($list);
        $this->addLimitToPaginationIfSpecified($pagination, $this->request);

        $results = $pagination->getCurrentPageResults();
        $resource = new Collection($results, new GroupTransformer(), Resources::RESOURCE_GROUPS);
        $cursor->describe($this->request, $results, $resource);

        return $resource;
    }

    /**
     * @OA\Post(
     *     path="/ccm/api/1.0/groups",
     *     tags={"groups"},
     *     operationId="addGroup",
     *     summary="Adds a Group object.",
     *     security={
     *         {"authorization": {"groups:add"}}
     *     },
     *     @OA\RequestBody(ref="#/components/requestBodies/NewGroup"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful groups operation",
     *         @OA\JsonContent(ref="#/components/schemas/NewGroup"),
     *     ),
     * )
     */
    public function add()
    {
        $folderManager = new FolderManager();
        $parentNode = $folderManager->getRootFolder();

        $pp = new \Permissions($parentNode);
        if (!$pp->canAddTreeSubNode()) {
            return $this->error(t('You do not have permission to add a group beneath %s', $parentNode->getTreeNodeDisplayName()), 403);
        }

        $data = json_decode($this->request->getContent(), true);

        $command = new AddGroupCommand();
        $command->setName($data['name']);
        $command->setParentNodeID($parentNode->getTreeNodeID());

        $group = $this->app->executeCommand($command);

        return new Item($group, new GroupTransformer(), Resources::RESOURCE_GROUPS);
    }


}
