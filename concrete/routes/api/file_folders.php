<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/file_folders', '\Concrete\Core\Api\Controller\FileFolders::listFileFolders')
    ->setScopes('files:read')
;

$router->get('/file_folders/{fileFolderID}', '\Concrete\Core\Api\Controller\FileFolders::read')
    ->setRequirement('fileFolderID', '[0-9]+')
    ->setScopes('files:read')
;
