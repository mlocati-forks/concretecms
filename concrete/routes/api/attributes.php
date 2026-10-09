<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/attribute_categories', '\Concrete\Core\Api\Controller\AttributeCategories::listAttributeCategories')
    ->setScopes('definitions:read')
;

$router->get('/attribute_categories/{category}/keys', '\Concrete\Core\Api\Controller\AttributeKeys::listAttributeKeys')
    ->setRequirement('category', '[A-Za-z0-9_@-]+')
    ->setScopes('definitions:read')
;

$router->get('/attribute_types', '\Concrete\Core\Api\Controller\AttributeTypes::listAttributeTypes')
    ->setScopes('definitions:read')
;
