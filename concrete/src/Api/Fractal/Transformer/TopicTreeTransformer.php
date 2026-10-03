<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\TopicTree as TopicTreeModel;
use Concrete\Core\Api\Tree\TreeNodes;
use Concrete\Core\Tree\Tree;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class TopicTreeTransformer extends TransformerAbstract
{
    /**
     * @var \Concrete\Core\Api\Tree\TreeNodes
     */
    protected $treeNodes;

    public function __construct(TreeNodes $treeNodes)
    {
        $this->treeNodes = $treeNodes;
    }

    /**
     * @return array<string,mixed>
     */
    public function transform(Tree $tree): array
    {
        $model = new TopicTreeModel();
        $model->id = (int) $tree->getTreeID();
        $model->name = (string) $tree->getTreeName();
        // the root of the tree stands for the tree itself, so what it holds is what the tree holds
        $model->nodes = (new TopicTreeNodeTransformer(false, $this->treeNodes))->transformChildren($tree->getRootTreeNodeObject());

        return $model->jsonSerialize();
    }
}
