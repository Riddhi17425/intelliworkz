<?php

/*
 * Entry point for cPanel hosts where the website folder (public_html) cannot be pointed at the application's "public" folder.
 *
 * Layout on the host:
 *     /home/CPANELUSER/intelliworkz-cms/     the application (app/, vendor/, .env, storage/ ...)  - NOT web accessible
 *     /home/CPANELUSER/public_html/          this index.php, .htaccess, assets/, admin-assets/, uploads/ ...
 *
 * If you named the application folder differently, change $appFolder.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$appFolder = 'intelliworkz-cms';
$base = dirname(__DIR__) . '/' . $appFolder;

if (file_exists($maintenance = $base . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $base . '/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once $base . '/bootstrap/app.php';

// pictures, css and uploaded files live in this folder, not in <application>/public
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
