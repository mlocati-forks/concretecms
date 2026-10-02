<?php

declare(strict_types=1);

namespace Concrete\Block\CoreStackDisplay;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The core_stack_display block type keeps in its table just the ID of the stack it shows.
 */
class Api extends DefaultBlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getCustomApiDescription()
     */
    public function getCustomApiDescription(): string
    {
        return 'Shows in a page the blocks of a stack, named by the ID that the stacks endpoint gives.';
    }
}
