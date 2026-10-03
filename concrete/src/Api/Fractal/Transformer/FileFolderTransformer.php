<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Tree\TreeNodes;
use Concrete\Core\Tree\Node\Node;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class FileFolderTransformer extends TransformerAbstract
{
    /**
     * The handle of the nodes of the file manager tree that hold the files.
     *
     * @var string
     */
    public const NODE_TYPE_FOLDER = 'file_folder';

    /**
     * The handle of the nodes of the file manager tree that are the files themselves.
     *
     * @var string
     */
    public const NODE_TYPE_FILE = 'file';

    /**
     * @var \Concrete\Core\Api\Tree\TreeNodes
     */
    protected $treeNodes;

    /**
     * @var bool
     */
    protected $askedForByItself;

    /**
     * @param bool $askedForByItself whether the answer is about this folder, which then says what
     *                               holds it and hands over the folders it holds, instead of saying
     *                               whether it holds any
     */
    public function __construct(TreeNodes $treeNodes, bool $askedForByItself = false)
    {
        $this->treeNodes = $treeNodes;
        $this->askedForByItself = $askedForByItself;
    }

    /**
     * Get what the API hands to its clients for a folder of files.
     *
     * @return array<string,mixed>
     */
    public function transform(Node $folder): array
    {
        $data = [
            'id' => (int) $folder->getTreeNodeID(),
            'name' => (string) $folder->getTreeNodeName(),
            'path' => (string) $folder->getTreeNodeDisplayPath(),
            'has_files' => $this->treeNodes->holdsChildren($folder, self::NODE_TYPE_FILE),
        ];
        if ($this->askedForByItself) {
            $data['parent_id'] = $this->treeNodes->getParentID($folder);
            $data['sub_folders'] = $this->getSubFolders($folder);
        } else {
            $data['has_sub_folders'] = $this->treeNodes->holdsChildren($folder, self::NODE_TYPE_FOLDER);
        }

        return $data;
    }

    /**
     * Get what the API hands to its clients for the folders a folder holds directly.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function getSubFolders(Node $folder): array
    {
        $transformer = new static($this->treeNodes);
        $subFolders = [];
        foreach ($this->treeNodes->getChildren($folder, self::NODE_TYPE_FOLDER) as $subFolder) {
            $subFolders[] = $transformer->transform($subFolder);
        }

        return $subFolders;
    }
}
