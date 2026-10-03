<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/thumbnail_types', '\Concrete\Core\Api\Controller\ThumbnailTypes::listThumbnailTypes')
    ->setScopes('definitions:read')
;

$router->get('/thumbnail_types/{thumbnailTypeID}', '\Concrete\Core\Api\Controller\ThumbnailTypes::read')
    ->setRequirement('thumbnailTypeID', '[0-9]+')
    ->setScopes('definitions:read')
;
