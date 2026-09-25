<?php

declare(strict_types=1);

namespace Concrete\Block\Search;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Page\Page;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends DefaultBlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        $baseSearchPath = (string) ($arguments['baseSearchPath'] ?? '');
        $arguments['baseSearchPath'] = empty($arguments['search_all']) ? 'EVERYWHERE' : 'ALL';
        if ($baseSearchPath !== '') {
            $page = Page::getByPath($baseSearchPath);
            if ($page && !$page->isError()) {
                $arguments['baseSearchPath'] = 'OTHER';
                $arguments['searchUnderCID'] = $page->getCollectionID();
            }
        }
        if ((int) ($arguments['postTo_cID'] ?? 0) !== 0) {
            $arguments['resultsPageKind'] = 'CID';
        } elseif ((string) ($arguments['resultsURL'] ?? '') !== '') {
            $arguments['resultsPageKind'] = 'URL';
        }
        $arguments['allowUserOptions'] = empty($arguments['allow_user_options']) ? 0 : 'ALLOW';

        return $arguments;
    }
}
