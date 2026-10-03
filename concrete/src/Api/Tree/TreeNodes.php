<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Tree;

use Concrete\Core\Permission\Checker;
use Concrete\Core\Tree\Node\Node;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The nodes of the trees that the API hands over, which the file folders and the topics both are.
 */
class TreeNodes
{
    /**
     * Get the nodes of a type that a node holds directly and the request may view.
     *
     * @return \Concrete\Core\Tree\Node\Node[]
     */
    public function getChildren(Node $node, string $typeHandle): array
    {
        $children = [];
        foreach ($node->getHierarchicalNodesOfType($typeHandle, 1, true, false, 1) as $row) {
            $child = $row['treeNodeObject'] ?? null;
            if ($child instanceof Node && $this->canView($child)) {
                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * Get every node that a node holds directly and the request may view, in the order of the tree.
     *
     * @return \Concrete\Core\Tree\Node\Node[]
     */
    public function getAllChildren(Node $node): array
    {
        $node->populateDirectChildrenOnly();
        $children = [];
        foreach ($node->getChildNodes() as $child) {
            if ($this->canView($child)) {
                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * Does a node hold any node at all? Unlike the children handed over, this counts the ones the
     * request may not view: it says what the node holds, not what the request can read of it.
     */
    public function holdsAnyChildren(Node $node): bool
    {
        return (int) $node->getTreeNodeChildCount() > 0;
    }

    /**
     * Does a node hold nodes of a type? Unlike the children handed over, this counts the ones the
     * request may not view: it says what the node holds, not what the request can read of it.
     */
    public function holdsChildren(Node $node, string $typeHandle): bool
    {
        // told to go no deeper, the core counts them instead of instantiating them
        $rows = $node->getHierarchicalNodesOfType($typeHandle, 1, false, true, 0);

        return (int) ($rows[0]['total'] ?? 0) > 0;
    }

    /**
     * May the user of the request view a node?
     */
    public function canView(Node $node): bool
    {
        // the checker answers through its magic call, which gives an integer
        return (bool) (new Checker($node))->canViewTreeNode();
    }

    /**
     * Is a node the root its tree hangs from, which stands for the tree itself?
     */
    public function isRoot(Node $node): bool
    {
        $tree = $node->getTreeObject();

        return $tree !== null && (int) $node->getTreeNodeID() === (int) $tree->getRootTreeNodeID();
    }

    /**
     * Get the ID of the node above this one, NULL for the root its tree hangs from.
     */
    public function getParentID(Node $node): ?int
    {
        $parentID = (int) $node->getTreeNodeParentID();

        return $parentID === 0 ? null : $parentID;
    }
}
