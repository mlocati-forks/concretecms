<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/boards', '\Concrete\Core\Api\Controller\Boards::listBoards')
    ->setScopes('boards:read')
;

$router->get('/boards/{boardID}', '\Concrete\Core\Api\Controller\Boards::read')
    ->setRequirement('boardID', '[0-9]+')
    ->setScopes('boards:read')
;

$router->get('/boards/{boardID}/instances', '\Concrete\Core\Api\Controller\Boards::listInstances')
    ->setRequirement('boardID', '[0-9]+')
    ->setScopes('boards:read')
;
