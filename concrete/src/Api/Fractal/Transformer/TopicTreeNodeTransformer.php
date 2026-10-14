<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\TopicTreeNode\Detail;
use Concrete\Core\Api\Model\TopicTreeNode\Summary;
use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Api\Tree\TreeNodes;
use Concrete\Core\Tree\Node\Node;
use League\Fractal\TransformerAbstract;

class TopicTreeNodeTransformer extends TransformerAbstract
{

    /**
     * The handle of the nodes of a topic tree that a page can be filed under.
     *
     * @var string
     */
    public const NODE_TYPE_TOPIC = 'topic';

    /**
     * The handle of the nodes of a topic tree that group topics.
     *
     * @var string
     */
    public const NODE_TYPE_CATEGORY = 'category';

    /**
     * @var \Concrete\Core\Api\Tree\TreeNodes
     */
    protected $treeNodes;

    /**
     * @var bool
     */
    protected $askedForByItself;

    /**
     * @param bool $askedForByItself whether the answer is about this node, which then says what holds
     *                               it and hands over the nodes it holds, instead of saying whether it
     *                               holds any
     */
    public function __construct(bool $askedForByItself = false, ?TreeNodes $treeNodes = null)
    {
        $this->askedForByItself = $askedForByItself;
        $this->treeNodes = $treeNodes ?: app(TreeNodes::class);
    }

    /**
     * @return array<string,mixed>
     */
    public function transform(Node $node)
    {
        if (!$this->isVisible($node)) {
            return ['id' => $node->getTreeNodeID()];
        }

        return $this->describe($node)->jsonSerialize();
    }

    /**
     * Get what a client reads of a node, which the node holding it hands over along with itself.
     *
     * @return \Concrete\Core\Api\Model\TopicTreeNode\Detail|\Concrete\Core\Api\Model\TopicTreeNode\Summary
     */
    public function describe(Node $node)
    {
        if ($this->askedForByItself) {
            $model = new Detail();
            $tree = $node->getTreeObject();
            $model->topic_tree_id = $tree === null ? null : (int) $tree->getTreeID();
            $parentID = $this->treeNodes->getParentID($node);
            // the root of a topic tree stands for the tree itself, and is no topic of its own
            $model->parent_id = $tree !== null && $parentID === (int) $tree->getRootTreeNodeID() ? null : $parentID;
            $model->nodes = $this->describeChildren($node);
        } else {
            $model = new Summary();
            $model->has_children = $this->treeNodes->holdsAnyChildren($node);
        }
        $model->id = (int) $node->getTreeNodeID();
        $model->name = (string) $node->getTreeNodeName();
        $model->path = (string) $node->getTreeNodeDisplayPath();
        $model->type = (string) $node->getTreeNodeTypeHandle();

        return $model;
    }

    /**
     * Get what the API hands to its clients for the nodes that a node holds directly, which in a
     * topic tree are topics and categories of them.
     *
     * @return \Concrete\Core\Api\Model\TopicTreeNode\Summary[]
     */
    public function describeChildren(Node $node)
    {
        $transformer = new static(false, $this->treeNodes);
        $children = [];
        foreach ($this->treeNodes->getAllChildren($node) as $child) {
            // nothing but these two is created in a topic tree, and nothing else would be a topic
            if (in_array($child->getTreeNodeTypeHandle(), [self::NODE_TYPE_TOPIC, self::NODE_TYPE_CATEGORY], true)) {
                $children[] = $transformer->describe($child);
            }
        }

        return $children;
    }

    /**
     * Whether the request may see the whole of a topic: the scopes of its token settle
     * it first, and then the permissions, the ones the endpoint serving these things asks about.
     */
    protected function isVisible(Node $node): bool
    {
        return app(TokenScopes::class)->has('definitions:read');
    }
}
