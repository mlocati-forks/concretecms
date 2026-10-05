<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="SystemInfo model",
 * )
 */
class SystemInfo
{
    /**
     * @OA\Property(title="Version of Concrete CMS this installation runs")
     *
     * @var string
     */
    private $version;

    /**
     * @OA\Property(title="Version of the core files found on disk, which differs from the installed one until the update is applied")
     *
     * @var string
     */
    private $code_version;

    /**
     * @OA\Property(title="Identifier of the last migration applied to the database")
     *
     * @var string
     */
    private $db_version;

    /**
     * @OA\Property(title="Packages installed in this site, with their version")
     *
     * @var string
     */
    private $packages;

    /**
     * @OA\Property(title="Files of this installation that take the place of the ones of the core")
     *
     * @var string
     */
    private $overrides;

    /**
     * @OA\Property(title="How the caches of this installation are configured, one setting per line")
     *
     * @var string
     */
    private $cache;

    /**
     * @OA\Property(title="Software serving this site, as it announces itself, empty where it says nothing")
     *
     * @var string
     */
    private $server;

    /**
     * @OA\Property(title="Interface PHP is run through here")
     *
     * @var string
     */
    private $server_api;

    /**
     * @OA\Property(title="Version of PHP running here")
     *
     * @var string
     */
    private $php_version;

    /**
     * @OA\Property(
     *     title="PHP extensions loaded here, FALSE where this installation does not let them be listed",
     *     oneOf={
     *         @OA\Schema(type="string"),
     *         @OA\Schema(type="boolean")
     *     }
     * )
     *
     * @var string|false
     */
    private $php_extensions;

    /**
     * @OA\Property(title="PHP settings of this installation, one per line")
     *
     * @var string
     */
    private $php_settings;
}
