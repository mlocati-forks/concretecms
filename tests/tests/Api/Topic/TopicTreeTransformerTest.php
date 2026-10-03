<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Topic;

use Concrete\Core\Api\Fractal\Transformer\TopicTreeNodeTransformer;
use Concrete\Core\Api\Fractal\Transformer\TopicTreeTransformer;
use Concrete\Core\Api\Tree\TreeNodes;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

class TopicTreeTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testATreeHandsOverWhatTheTopOfItHolds(): void
    {
        $topic = $this->createNode(82, 'Marketing', '/Marketing', TopicTreeNodeTransformer::NODE_TYPE_TOPIC);
        $tree = $this->createTree(5, 'Topics');

        $transformed = (new TopicTreeTransformer($this->createTreeNodes([$topic])))->transform($tree);

        static::assertAnswerIs([
            'id' => 5,
            'name' => 'Topics',
            'nodes' => [
                // the nodes at the top say what they hold, and nothing of what is below them
                ['id' => 82, 'name' => 'Marketing', 'path' => '/Marketing', 'type' => 'topic', 'has_children' => true],
            ],
        ], $transformed);
        $this->assertFieldsAre('TopicTree', $transformed);
    }

    public function testATreeHoldingNothingHandsOverNoNode(): void
    {
        $transformed = (new TopicTreeTransformer($this->createTreeNodes([])))->transform($this->createTree(5, 'Topics'));

        static::assertSame([], $transformed['nodes']);
    }

    /**
     * @param \Concrete\Core\Tree\Node\Node[] $children the nodes the root of the tree holds.
     *
     * @return \Concrete\Core\Api\Tree\TreeNodes&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createTreeNodes(array $children): TreeNodes
    {
        $treeNodes = $this->createMock(TreeNodes::class);
        $treeNodes->method('getAllChildren')->willReturn($children);
        $treeNodes->method('holdsAnyChildren')->willReturn(true);

        return $treeNodes;
    }

    private function createTree(int $id, string $name): TopicTree
    {
        $tree = $this->createMock(TopicTree::class);
        $tree->method('getTreeID')->willReturn($id);
        $tree->method('getTreeName')->willReturn($name);
        // the root of the tree stands for the tree itself, and is handed over to no client
        $tree->method('getRootTreeNodeObject')->willReturn($this->createMock(Node::class));

        return $tree;
    }

    private function createNode(int $id, string $name, string $path, string $typeHandle): Node
    {
        $node = $this->createMock(Node::class);
        $node->method('getTreeNodeID')->willReturn($id);
        $node->method('getTreeNodeName')->willReturn($name);
        $node->method('getTreeNodeDisplayPath')->willReturn($path);
        $node->method('getTreeNodeTypeHandle')->willReturn($typeHandle);

        return $node;
    }
}
