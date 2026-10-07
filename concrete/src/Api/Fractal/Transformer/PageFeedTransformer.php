<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\PageFeed as PageFeedModel;
use Concrete\Core\Entity\Page\Feed;
use Concrete\Core\Page\Type\Type as PageType;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class PageFeedTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Feed $feed): array
    {
        $parentPageID = (int) $feed->getParentID();
        $carriesAnArea = $feed->getTypeOfContentToDisplay() !== 'S';

        $model = new PageFeedModel();
        $model->id = (int) $feed->getID();
        $model->handle = (string) $feed->getHandle();
        $model->title = (string) $feed->getTitle();
        $model->description = (string) $feed->getDescription();
        $model->url = (string) $feed->getFeedURL();
        $model->parent_page_id = $parentPageID === 0 ? null : $parentPageID;
        $model->include_all_descendants = (bool) $feed->getIncludeAllDescendents();
        $model->page_type = $this->getPageTypeHandle($feed);
        $model->only_featured = (bool) $feed->getDisplayFeaturedOnly();
        $model->include_aliases = (bool) $feed->getDisplayAliases();
        $model->include_system_pages = (bool) $feed->getDisplaySystemPages();
        $model->content = $carriesAnArea ? 'area' : 'description';
        $model->area = $carriesAnArea ? (string) $feed->getAreaHandleToDisplay() : '';

        return $model->jsonSerialize();
    }

    /**
     * @return string an empty string where the feed is limited to no page type, or names one that is gone
     */
    protected function getPageTypeHandle(Feed $feed): string
    {
        $pageTypeID = (int) $feed->getPageTypeID();
        if ($pageTypeID === 0) {
            return '';
        }
        $pageType = PageType::getByID($pageTypeID);

        return $pageType === null ? '' : (string) $pageType->getPageTypeHandle();
    }
}
