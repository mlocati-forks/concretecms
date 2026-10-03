<?php

declare(strict_types=1);

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Application\Application $app
 * @var Concrete\Core\Routing\Router $router
 */

$router->get('/topic_trees', '\Concrete\Core\Api\Controller\TopicTrees::listTopicTrees')
    ->setScopes('definitions:read')
;

$router->get('/topic_trees/{topicTreeID}', '\Concrete\Core\Api\Controller\TopicTrees::read')
    ->setRequirement('topicTreeID', '[0-9]+')
    ->setScopes('definitions:read')
;

$router->get('/topic_tree_nodes/{topicTreeNodeID}', '\Concrete\Core\Api\Controller\TopicTreeNodes::read')
    ->setRequirement('topicTreeNodeID', '[0-9]+')
    ->setScopes('definitions:read')
;
