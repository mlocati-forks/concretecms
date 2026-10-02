<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/layout_presets', '\Concrete\Core\Api\Controller\LayoutPresets::listLayoutPresets')
    ->setScopes('definitions:read')
;
