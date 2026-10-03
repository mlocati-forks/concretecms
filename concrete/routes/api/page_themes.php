<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/page_themes', '\Concrete\Core\Api\Controller\PageThemes::listPageThemes')
    ->setScopes('definitions:read')
;

$router->get('/page_themes/{pageThemeHandle}', '\Concrete\Core\Api\Controller\PageThemes::read')
    ->setScopes('definitions:read')
;
