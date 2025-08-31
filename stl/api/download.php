<?php
$jobId = $_GET['job_id'] ?? '';
$path = __DIR__ . '/../storage/exports/' . basename($jobId) . '.stl';
if (!is_file($path)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}
header('Content-Type: application/sla');
header('Content-Disposition: attachment; filename="' . $jobId . '.stl"');
readfile($path);
