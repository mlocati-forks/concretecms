<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Carbon\Carbon;
use Concrete\Core\Api\Model\BoardInstance as BoardInstanceModel;
use Concrete\Core\Entity\Board\Instance;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class BoardInstanceTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Instance $instance): array
    {
        return $this->describe($instance)->jsonSerialize();
    }

    /**
     * Get what a client reads of an instance of a board.
     */
    public function describe(Instance $instance): BoardInstanceModel
    {
        $model = new BoardInstanceModel();
        $model->id = (int) $instance->getBoardInstanceID();
        $model->name = (string) $instance->getBoardInstanceName();
        $model->date_created = Carbon::make($instance->getDateCreatedObject())->toAtomString();
        // an instance that was never laid out keeps no date of it
        $model->date_last_generated = $instance->getDateLastGenerated() === null
            ? null
            : Carbon::make($instance->getDateLastGeneratedObject())->toAtomString();

        return $model;
    }
}
