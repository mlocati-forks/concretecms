<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

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
     * Get what the API hands to its clients for a tree of topics.
     *
     * @return array<string,mixed>
     */
    public function transform(Tree $tree): array
    {
        return [
            'id' => (int) $tree->getTreeID(),
            'name' => (string) $tree->getTreeName(),
            // the root of the tree stands for the tree itself, so what it holds is what the tree holds
            'nodes' => (new TopicTreeNodeTransformer(false, $this->treeNodes))->transformChildren($tree->getRootTreeNodeObject()),
        ];
    }
}
