<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\Board as BoardModel;
use Concrete\Core\Entity\Board\Board;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class BoardTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Board $board): array
    {
        return $this->describe($board)->jsonSerialize();
    }

    /**
     * Get what a client reads of a board.
     */
    public function describe(Board $board): BoardModel
    {
        $model = new BoardModel();
        $model->id = (int) $board->getBoardID();
        $model->name = (string) $board->getBoardName();
        $site = $board->getSite();
        $model->site_id = $site === null ? null : (int) $site->getSiteID();

        return $model;
    }
}
