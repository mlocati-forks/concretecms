<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/containers', '\Concrete\Core\Api\Controller\Containers::listContainers')
    ->setScopes('definitions:read')
;

$router->get('/containers/{containerHandle}', '\Concrete\Core\Api\Controller\Containers::read')
    ->setScopes('definitions:read')
;
