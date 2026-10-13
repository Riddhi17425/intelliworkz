<?php

/*
 * Builds everything that has to be uploaded to a cPanel host. Run it on your own computer:
 *
 *     php deploy/build.php
 *
 * It leaves these files in deploy/dist/ (nothing in the project itself is changed):
 *   intelliworkz-app.zip      the application (Laravel + vendor/ without dev packages, no pictures)
 *   intelliworkz-assets.zip   public/assets (logos, photos, icons, 3D models – the big one)
 *   database.sql              the database as it is now (structure + content, no inquiries/logs/sessions)
 *   env-production.txt        a ready .env for the live site with a fresh APP_KEY (fill in 4 values, rename to .env)
 *   public_html/              index.php + .htaccess for hosts where the web folder cannot be pointed at /public
 *
 * Options:  --with-inquiries   keep the contact inquiries and the activity log in database.sql
 *           --skip-composer    reuse an earlier deploy/build/app (faster, only for repeated builds)
 */

$root = dirname(__DIR__);
$build = "$root/deploy/build/app";
$dist = "$root/deploy/dist";
$withInquiries = in_array('--with-inquiries', $argv, true);
$skipComposer = in_array('--skip-composer', $argv, true);

function step(string $text): void { echo "\n== $text\n"; }

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
        $f->isDir() && !$f->isLink() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}

/** Copy $from to $to, leaving out every path (relative, forward slashes) that matches one of $skip. */
function copyTree(string $from, string $to, array $skip): int
{
    $count = 0;
    $from = rtrim(str_replace('\\', '/', $from), '/');
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($it as $item) {
        $rel = ltrim(substr(str_replace('\\', '/', $item->getPathname()), strlen($from)), '/');
        foreach ($skip as $pattern) {
            if (fnmatch($pattern, $rel) || fnmatch($pattern . '/*', $rel)) continue 2;
        }
        $target = "$to/$rel";
        if ($item->isDir()) {
            is_dir($target) || mkdir($target, 0777, true);
        } else {
            is_dir(dirname($target)) || mkdir(dirname($target), 0777, true);
            copy($item->getPathname(), $target);
            $count++;
        }
    }
    return $count;
}

/** Zip a folder with forward-slash entry names (Windows' Compress-Archive writes backslashes that break on Linux hosts). */
function zipTree(string $dir, string $zipFile, string $prefix = '', array $extraDirs = []): int
{
    @unlink($zipFile);
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) !== true) throw new RuntimeException("cannot create $zipFile");
    $dir = rtrim(str_replace('\\', '/', $dir), '/');
    $n = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
        $rel = ltrim(substr(str_replace('\\', '/', $item->getPathname()), strlen($dir)), '/');
        $name = $prefix . $rel;
        if ($item->isDir()) {
            $zip->addEmptyDir($name);
        } else {
            $zip->addFile($item->getPathname(), $name);
            $n++;
        }
    }
    foreach ($extraDirs as $d) $zip->addEmptyDir($d);
    $zip->close();
    return $n;
}

function env(string $root, string $key, string $default = ''): string
{
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        foreach (file("$root/.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if ($line[0] === '#' || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $vars[trim($k)] = trim($v, " \t\"'");
        }
    }
    return $vars[$key] ?? $default;
}

/* ------------------------------------------------------------------ 1. the application */
step('Copying the application');
if (!$skipComposer) {
    rrmdir($build);
    mkdir($build, 0777, true);
    $n = copyTree($root, $build, [
        '.git', '.github', '.idea', '.vscode', 'node_modules', 'vendor', 'tests', 'deploy', 'tools', 'docs', '.env', '.env.*', '.phpunit.*',
        'phpunit.xml', 'package.json', 'package-lock.json', 'vite.config.js', 'public/assets', 'public/uploads/*', 'public/storage', 'public/hot',
        'storage/logs/*', 'storage/framework/cache/*', 'storage/framework/sessions/*', 'storage/framework/views/*', 'storage/framework/testing',
        'storage/pail', 'bootstrap/cache/*.php', 'README.md',
    ]);
    echo "   $n files\n";
}

step('Installing PHP packages without dev tools (composer install --no-dev)');
if (!$skipComposer) {
    chdir($build);
    passthru('composer install --no-dev --optimize-autoloader --no-interaction --no-progress 2>&1', $code);
    if ($code !== 0) { fwrite(STDERR, "composer failed\n"); exit(1); }
}

// folders Laravel writes to must exist (their content is not shipped)
foreach (['storage/logs', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/app/public', 'storage/app/private', 'bootstrap/cache', 'public/uploads'] as $d) {
    is_dir("$build/$d") || mkdir("$build/$d", 0777, true);
    file_put_contents("$build/$d/.gitignore", "*\n!.gitignore\n");
}

is_dir($dist) || mkdir($dist, 0777, true);
foreach (glob("$dist/*") as $old) { is_dir($old) ? rrmdir($old) : unlink($old); }

step('Zipping the application');
$n = zipTree($build, "$dist/intelliworkz-app.zip");
echo "   $n files, " . round(filesize("$dist/intelliworkz-app.zip") / 1048576, 1) . " MB\n";

step('Zipping public/assets (pictures, icons, fonts, 3D models)');
$n = zipTree("$root/public/assets", "$dist/intelliworkz-assets.zip", 'assets/');
echo "   $n files, " . round(filesize("$dist/intelliworkz-assets.zip") / 1048576, 1) . " MB\n";

/* ------------------------------------------------------------------ 2. the database */
step('Exporting the database');
$mysqldump = null;
foreach (['C:/xampp/mysql/bin/mysqldump.exe', 'mysqldump'] as $c) { if ($c === 'mysqldump' || is_file($c)) { $mysqldump = $c; break; } }
$db = env($root, 'DB_DATABASE', 'intelliworkz_cms');
putenv('MYSQL_PWD=' . env($root, 'DB_PASSWORD'));
$base = escapeshellarg($mysqldump) . ' --host=' . escapeshellarg(env($root, 'DB_HOST', '127.0.0.1')) . ' --port=' . escapeshellarg(env($root, 'DB_PORT', '3306'))
    . ' --user=' . escapeshellarg(env($root, 'DB_USERNAME', 'root')) . ' --default-character-set=utf8mb4 --no-tablespaces --skip-comments --skip-lock-tables';
$volatile = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];
if (!$withInquiries) array_push($volatile, 'contact_inquiries', 'activity_logs');

$tmpData = "$dist/_data.sql";
$tmpStruct = "$dist/_struct.sql";
$ignore = implode(' ', array_map(fn ($t) => '--ignore-table=' . escapeshellarg("$db.$t"), $volatile));
passthru("$base --single-transaction --add-drop-table $ignore " . escapeshellarg($db) . ' > ' . escapeshellarg($tmpData), $c1);
passthru("$base --no-data --add-drop-table " . escapeshellarg($db) . ' ' . implode(' ', array_map('escapeshellarg', $volatile)) . ' > ' . escapeshellarg($tmpStruct), $c2);
if ($c1 !== 0 || $c2 !== 0) { fwrite(STDERR, "mysqldump failed (is MySQL running and .env correct?)\n"); exit(1); }
file_put_contents("$dist/database.sql",
    "-- Intelliworkz CMS database export " . date('Y-m-d H:i') . "\n-- Import with phpMyAdmin (cPanel) into an EMPTY database.\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n"
    . file_get_contents($tmpData) . "\n" . file_get_contents($tmpStruct) . "\nSET FOREIGN_KEY_CHECKS=1;\n");
unlink($tmpData); unlink($tmpStruct);
echo '   ' . round(filesize("$dist/database.sql") / 1024) . " KB" . ($withInquiries ? ' (inquiries and activity log included)' : ' (inquiries, activity log, sessions and cache left out)') . "\n";

/* ------------------------------------------------------------------ 3. .env for the live site, entry point for hosts without a /public document root */
step('Writing env-production.txt and the public_html files');
$key = 'base64:' . base64_encode(random_bytes(32));
file_put_contents("$dist/env-production.txt", str_replace('__APP_KEY__', $key, file_get_contents(__DIR__ . '/env.production.template')));

mkdir("$dist/public_html", 0777, true);
copy(__DIR__ . '/public_html.index.php', "$dist/public_html/index.php");
copy("$root/public/.htaccess", "$dist/public_html/.htaccess");

echo "\nDone. Upload what is in deploy/dist/ following docs/DEPLOY_CPANEL.md\n";
foreach (glob("$dist/*") as $f) echo '   ' . str_pad(basename($f), 28) . (is_dir($f) ? '(folder)' : round(filesize($f) / 1048576, 2) . ' MB') . "\n";
