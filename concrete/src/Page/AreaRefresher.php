<?php

declare(strict_types=1);

namespace Concrete\Core\Page;

use Concrete\Core\Http\Request;
use Concrete\Core\Page\View\PageView;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The areas of a page are records that only whatever draws it creates: this draws one and throws the HTML
 * away.
 */
class AreaRefresher
{
    /**
     * @throws \Throwable whatever drawing the page raises, the page having no view of its own included
     */
    public function refresh(Page $page): void
    {
        $view = $page->getPageController()->getViewObject();
        if (!$view instanceof PageView) {
            throw new \RuntimeException(t('The page %s has no view to draw it with.', $page->getCollectionID()));
        }
        $bufferLevel = ob_get_level();
        // a template, and the blocks it draws, ask the request which page is being shown
        $request = Request::getInstance();
        $shownPage = $request->getCurrentPage();
        $request->setCurrentPage($page);
        try {
            $view->render();
        } finally {
            if ($shownPage === null) {
                $request->clearCurrentPage();
            } else {
                $request->setCurrentPage($shownPage);
            }
            // what a half-drawn page left buffered would otherwise leak into the answer
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }
    }
}
