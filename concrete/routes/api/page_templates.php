<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/page_templates', '\Concrete\Core\Api\Controller\PageTemplates::listPageTemplates')
    ->setScopes('definitions:read')
;
