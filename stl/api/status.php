<?php
header('Content-Type: application/json');
$jobId = $_GET['job_id'] ?? '';
$db = new PDO('sqlite:' . __DIR__ . '/../db/stl.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stm = $db->prepare('SELECT status, download_url FROM jobs WHERE id = :id');
$stm->execute([':id' => $jobId]);
$row = $stm->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo json_encode(['ok' => true, 'status' => $row['status'], 'download_url' => $row['download_url']]);
} else {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => ['message' => 'Job not found']]);
}
