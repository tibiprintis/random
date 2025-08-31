<?php
$dir = __DIR__ . '/../storage/exports';
$files = glob($dir . '/*.stl');
$now = time();
foreach ($files as $f) {
    if ($now - filemtime($f) > 86400 * 3) {
        @unlink($f);
    }
}
echo 'ok';
