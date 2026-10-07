<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/page_feeds', '\Concrete\Core\Api\Controller\PageFeeds::listPageFeeds')
    ->setScopes('definitions:read')
;

$router->get('/page_feeds/{pageFeedID}', '\Concrete\Core\Api\Controller\PageFeeds::read')
    ->setRequirement('pageFeedID', '[0-9]+')
    ->setScopes('definitions:read')
;
