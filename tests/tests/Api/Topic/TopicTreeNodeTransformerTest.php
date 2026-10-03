<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Topic;

use Concrete\Core\Api\Fractal\Transformer\TopicTreeNodeTransformer;
use Concrete\Core\Api\Tree\TreeNodes;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests what the topics endpoints hand to their clients.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\TopicTreeNodeTransformer
 */
class TopicTreeNodeTransformerTest extends TestCase
{
    public function testATopicSaysWhichTreeAndWhichTopicHoldIt(): void
    {
        $topic = $this->createNode(83, 'Sales', '/Marketing/Sales', TopicTreeNodeTransformer::NODE_TYPE_TOPIC, 82);
        $treeNodes = $this->createTreeNodes(82, false, []);

        static::assertSame([
            'id' => 83,
            'name' => 'Sales',
            'path' => '/Marketing/Sales',
            'type' => 'topic',
            'topic_tree_id' => 5,
            'parent_id' => 82,
            'nodes' => [],
        ], (new TopicTreeNodeTransformer(true, $treeNodes))->transform($topic));
    }

    public function testATopicAtTheTopOfItsTreeIsHeldByNoTopic(): void
    {
        $topic = $this->createNode(82, 'Marketing', '/Marketing', TopicTreeNodeTransformer::NODE_TYPE_TOPIC, 81);
        // 81 is the root of the tree, which stands for the tree itself
        $treeNodes = $this->createTreeNodes(81, false, []);

        static::assertNull((new TopicTreeNodeTransformer(true, $treeNodes))->transform($topic)['parent_id']);
    }

    public function testTheTopicsOfACategoryComeWithItWhereTheyAreAskedFor(): void
    {
        $category = $this->createNode(16, 'Products', '/Products', TopicTreeNodeTransformer::NODE_TYPE_CATEGORY, 81);
        $topic = $this->createNode(17, 'Keyboards', '/Products/Keyboards', TopicTreeNodeTransformer::NODE_TYPE_TOPIC, 16);
        $treeNodes = $this->createTreeNodes(81, true, [$topic]);

        $transformed = (new TopicTreeNodeTransformer(true, $treeNodes))->transform($category);

        // the nodes handed over say it themselves, so the node answering doesn't repeat it
        static::assertArrayNotHasKey('has_children', $transformed);
        static::assertSame([
            // they say what they hold, and neither which topic nor which tree holds them
            ['id' => 17, 'name' => 'Keyboards', 'path' => '/Products/Keyboards', 'type' => 'topic', 'has_children' => true],
        ], $transformed['nodes']);
    }

    public function testWhatATopicTreeHoldsThatIsNoTopicIsLeftOut(): void
    {
        $category = $this->createNode(16, 'Products', '/Products', TopicTreeNodeTransformer::NODE_TYPE_CATEGORY, 81);
        $topic = $this->createNode(17, 'Keyboards', '/Products/Keyboards', TopicTreeNodeTransformer::NODE_TYPE_TOPIC, 16);
        $stranger = $this->createNode(18, 'Editors', '/Products/Editors', 'group', 16);
        $treeNodes = $this->createTreeNodes(81, true, [$topic, $stranger]);

        $transformed = (new TopicTreeNodeTransformer(true, $treeNodes))->transform($category);

        static::assertSame([17], array_column($transformed['nodes'], 'id'));
    }

    /**
     * @param \Concrete\Core\Tree\Node\Node[] $children the nodes a node is to hand over
     *
     * @return \Concrete\Core\Api\Tree\TreeNodes&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createTreeNodes(int $parentID, bool $holdsChildren, array $children): TreeNodes
    {
        $treeNodes = $this->createMock(TreeNodes::class);
        $treeNodes->method('getParentID')->willReturn($parentID);
        $treeNodes->method('holdsAnyChildren')->willReturn($holdsChildren);
        $treeNodes->method('getAllChildren')->willReturn($children);

        return $treeNodes;
    }

    private function createNode(int $id, string $name, string $path, string $typeHandle, int $parentID): Node
    {
        $tree = $this->createMock(TopicTree::class);
        $tree->method('getTreeID')->willReturn(5);
        // the tree hangs from the node the topics at the top of it are held by
        $tree->method('getRootTreeNodeID')->willReturn(81);
        $node = $this->createMock(Node::class);
        $node->method('getTreeNodeID')->willReturn($id);
        $node->method('getTreeNodeName')->willReturn($name);
        $node->method('getTreeNodeDisplayPath')->willReturn($path);
        $node->method('getTreeNodeTypeHandle')->willReturn($typeHandle);
        $node->method('getTreeNodeParentID')->willReturn($parentID);
        $node->method('getTreeObject')->willReturn($tree);

        return $node;
    }
}
