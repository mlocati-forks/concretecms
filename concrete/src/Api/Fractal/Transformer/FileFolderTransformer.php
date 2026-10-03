<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\FileFolder\Detail;
use Concrete\Core\Api\Model\FileFolder\Summary;
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
     * @return array<string,mixed>
     */
    public function transform(Node $folder): array
    {
        if ($this->askedForByItself) {
            $model = new Detail();
            $model->parent_id = $this->treeNodes->getParentID($folder);
            $model->sub_folders = $this->getSubFolders($folder);
        } else {
            $model = new Summary();
            $model->has_sub_folders = $this->treeNodes->holdsChildren($folder, self::NODE_TYPE_FOLDER);
        }
        $model->id = (int) $folder->getTreeNodeID();
        $model->name = (string) $folder->getTreeNodeName();
        $model->path = (string) $folder->getTreeNodeDisplayPath();
        $model->has_files = $this->treeNodes->holdsChildren($folder, self::NODE_TYPE_FILE);

        return $model->jsonSerialize();
    }

    /**
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
