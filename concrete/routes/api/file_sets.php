<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/file_sets', '\Concrete\Core\Api\Controller\FileSets::listFileSets')
    ->setScopes('files:read')
;

$router->get('/file_sets/{fileSetID}', '\Concrete\Core\Api\Controller\FileSets::read')
    ->setRequirement('fileSetID', '[0-9]+')
    ->setScopes('files:read')
;
