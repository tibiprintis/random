<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => ['message' => 'Method not allowed']]);
    exit;
}

if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => ['message' => 'Invalid CSRF']]);
    exit;
}

$svgContent = '';
if (!empty($_FILES['file']['tmp_name'])) {
    $svgContent = file_get_contents($_FILES['file']['tmp_name']);
} elseif (!empty($_POST['svg'])) {
    $svgContent = $_POST['svg'];
}

if ($svgContent === '') {
    echo json_encode(['ok' => false, 'error' => ['message' => 'No SVG provided']]);
    exit;
}

$params = [
    'width_mm' => (float)($_POST['width_mm'] ?? 0),
    'height_mm' => (float)($_POST['height_mm'] ?? 0),
    'offset_mm' => (float)($_POST['offset_mm'] ?? 0),
    'segments'  => (int)($_POST['segments'] ?? 32)
];

$cacheKey = hash('sha256', $svgContent . json_encode($params));
$jobId = $cacheKey;
$exportDir = __DIR__ . '/../storage/exports';
$downloadUrl = '../api/download.php?job_id=' . $jobId;
$stlPath = "$exportDir/$jobId.stl";

if (file_exists($stlPath)) {
    echo json_encode(['ok' => true, 'status' => 'cached', 'job_id' => $jobId, 'download_url' => $downloadUrl]);
    exit;
}

// Parse SVG safely
libxml_use_internal_errors(true);
$svg = simplexml_load_string($svgContent, 'SimpleXMLElement', LIBXML_NONET);
if (!$svg) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => ['message' => 'Invalid SVG']]);
    exit;
}

$svgWidthAttr  = (string)$svg['width'];
$svgHeightAttr = (string)$svg['height'];
$svgWidth  = floatval($svgWidthAttr ?: '100');
$svgHeight = floatval($svgHeightAttr ?: '100');

function cubicPoint($p0, $p1, $p2, $p3, $t) {
    $mt = 1 - $t;
    return [
        $mt ** 3 * $p0[0] + 3 * $mt ** 2 * $t * $p1[0] + 3 * $mt * $t ** 2 * $p2[0] + $t ** 3 * $p3[0],
        $mt ** 3 * $p0[1] + 3 * $mt ** 2 * $t * $p1[1] + 3 * $mt * $t ** 2 * $p2[1] + $t ** 3 * $p3[1]
    ];
}
function quadPoint($p0, $p1, $p2, $t) {
    $mt = 1 - $t;
    return [
        $mt * $mt * $p0[0] + 2 * $mt * $t * $p1[0] + $t * $t * $p2[0],
        $mt * $mt * $p0[1] + 2 * $mt * $t * $p1[1] + $t * $t * $p2[1]
    ];
}
function pathToPoints($d, $curveSegs) {
    $pattern = '/([MLHVCSQTZmlhvcsqtz])([^MLHVCSQTZmlhvcsqtz]*)/';
    preg_match_all($pattern, $d, $matches, PREG_SET_ORDER);
    $pts = [];
    $cursor = [0, 0];
    $start = [0, 0];
    $lastCtrl = [0, 0];
    foreach ($matches as $m) {
        $cmd = $m[1];
        $params = array_filter(preg_split('/[ ,]+/', trim($m[2])));
        $nums = array_map('floatval', $params);
        switch ($cmd) {
            case 'M':
            case 'm':
                for ($i = 0; $i < count($nums); $i += 2) {
                    $x = $nums[$i] + ($cmd === 'm' ? $cursor[0] : 0);
                    $y = $nums[$i+1] + ($cmd === 'm' ? $cursor[1] : 0);
                    $cursor = [$x, $y];
                    if (empty($pts)) $start = $cursor;
                    $pts[] = $cursor;
                }
                break;
            case 'L':
            case 'l':
                for ($i = 0; $i < count($nums); $i += 2) {
                    $x = $nums[$i] + ($cmd === 'l' ? $cursor[0] : 0);
                    $y = $nums[$i+1] + ($cmd === 'l' ? $cursor[1] : 0);
                    $cursor = [$x, $y];
                    $pts[] = $cursor;
                }
                break;
            case 'H':
            case 'h':
                foreach ($nums as $x) {
                    $x = $x + ($cmd === 'h' ? $cursor[0] : 0);
                    $cursor = [$x, $cursor[1]];
                    $pts[] = $cursor;
                }
                break;
            case 'V':
            case 'v':
                foreach ($nums as $y) {
                    $y = $y + ($cmd === 'v' ? $cursor[1] : 0);
                    $cursor = [$cursor[0], $y];
                    $pts[] = $cursor;
                }
                break;
            case 'C':
            case 'c':
                for ($i = 0; $i < count($nums); $i += 6) {
                    $p1 = [$nums[$i] + ($cmd === 'c' ? $cursor[0] : 0), $nums[$i+1] + ($cmd === 'c' ? $cursor[1] : 0)];
                    $p2 = [$nums[$i+2] + ($cmd === 'c' ? $cursor[0] : 0), $nums[$i+3] + ($cmd === 'c' ? $cursor[1] : 0)];
                    $p3 = [$nums[$i+4] + ($cmd === 'c' ? $cursor[0] : 0), $nums[$i+5] + ($cmd === 'c' ? $cursor[1] : 0)];
                    for ($t = 1; $t <= $curveSegs; $t++) {
                        $pts[] = cubicPoint($cursor, $p1, $p2, $p3, $t / $curveSegs);
                    }
                    $cursor = $p3;
                    $lastCtrl = $p2;
                }
                break;
            case 'S':
            case 's':
                for ($i = 0; $i < count($nums); $i += 4) {
                    $p1 = [$cursor[0] * 2 - $lastCtrl[0], $cursor[1] * 2 - $lastCtrl[1]];
                    $p2 = [$nums[$i] + ($cmd === 's' ? $cursor[0] : 0), $nums[$i+1] + ($cmd === 's' ? $cursor[1] : 0)];
                    $p3 = [$nums[$i+2] + ($cmd === 's' ? $cursor[0] : 0), $nums[$i+3] + ($cmd === 's' ? $cursor[1] : 0)];
                    for ($t = 1; $t <= $curveSegs; $t++) {
                        $pts[] = cubicPoint($cursor, $p1, $p2, $p3, $t / $curveSegs);
                    }
                    $cursor = $p3;
                    $lastCtrl = $p2;
                }
                break;
            case 'Q':
            case 'q':
                for ($i = 0; $i < count($nums); $i += 4) {
                    $p1 = [$nums[$i] + ($cmd === 'q' ? $cursor[0] : 0), $nums[$i+1] + ($cmd === 'q' ? $cursor[1] : 0)];
                    $p2 = [$nums[$i+2] + ($cmd === 'q' ? $cursor[0] : 0), $nums[$i+3] + ($cmd === 'q' ? $cursor[1] : 0)];
                    for ($t = 1; $t <= $curveSegs; $t++) {
                        $pts[] = quadPoint($cursor, $p1, $p2, $t / $curveSegs);
                    }
                    $cursor = $p2;
                    $lastCtrl = $p1;
                }
                break;
            case 'T':
            case 't':
                for ($i = 0; $i < count($nums); $i += 2) {
                    $p1 = [$cursor[0] * 2 - $lastCtrl[0], $cursor[1] * 2 - $lastCtrl[1]];
                    $p2 = [$nums[$i] + ($cmd === 't' ? $cursor[0] : 0), $nums[$i+1] + ($cmd === 't' ? $cursor[1] : 0)];
                    for ($t = 1; $t <= $curveSegs; $t++) {
                        $pts[] = quadPoint($cursor, $p1, $p2, $t / $curveSegs);
                    }
                    $cursor = $p2;
                    $lastCtrl = $p1;
                }
                break;
            case 'Z':
            case 'z':
                $cursor = $start;
                $pts[] = $start;
                break;
        }
    }
    return $pts;
}

// Extract points from first polyline/path
$points = [];
if (isset($svg->polyline[0])) {
    $raw = preg_split('/\s+/', trim((string)$svg->polyline[0]['points']));
    foreach ($raw as $pair) {
        if (strpos($pair, ',') !== false) {
            [$x, $y] = array_map('floatval', explode(',', $pair));
            $points[] = [$x, $y];
        }
    }
} elseif (isset($svg->path[0])) {
    $d = (string)$svg->path[0]['d'];
    $points = pathToPoints($d, max(4, $params['segments']));
}

if (count($points) < 2) {
    echo json_encode(['ok' => false, 'error' => ['message' => 'SVG lacks polyline/path data']]);
    exit;
}

// Scale to mm
$scaleX = $params['width_mm']  > 0 ? $params['width_mm']  / $svgWidth  : 1;
$scaleY = $params['height_mm'] > 0 ? $params['height_mm'] / $svgHeight : 1;
foreach ($points as &$p) {
    $p[0] *= $scaleX;
    $p[1] *= $scaleY;
}
unset($p);

$minX = min(array_column($points, 0));
$axis = $minX + $params['offset_mm'];
$segments = max(3, $params['segments']);

$triangles = [];
for ($i = 0; $i < count($points) - 1; $i++) {
    [$x1, $y1] = $points[$i];
    [$x2, $y2] = $points[$i + 1];
    $r1 = $x1 - $axis;
    $r2 = $x2 - $axis;
    for ($s = 0; $s < $segments; $s++) {
        $t1 = 2 * M_PI * ($s / $segments);
        $t2 = 2 * M_PI * (($s + 1) / $segments);
        $a1 = [$axis + $r1 * cos($t1), $y1, $r1 * sin($t1)];
        $b1 = [$axis + $r1 * cos($t2), $y1, $r1 * sin($t2)];
        $a2 = [$axis + $r2 * cos($t1), $y2, $r2 * sin($t1)];
        $b2 = [$axis + $r2 * cos($t2), $y2, $r2 * sin($t2)];
        $triangles[] = [$a1, $a2, $b1];
        $triangles[] = [$b1, $a2, $b2];
    }
}

// Helper to compute normal
$normal = function ($a, $b, $c) {
    $u = [$b[0] - $a[0], $b[1] - $a[1], $b[2] - $a[2]];
    $v = [$c[0] - $a[0], $c[1] - $a[1], $c[2] - $a[2]];
    $n = [
        $u[1] * $v[2] - $u[2] * $v[1],
        $u[2] * $v[0] - $u[0] * $v[2],
        $u[0] * $v[1] - $u[1] * $v[0]
    ];
    $len = sqrt($n[0] ** 2 + $n[1] ** 2 + $n[2] ** 2) ?: 1;
    return [$n[0] / $len, $n[1] / $len, $n[2] / $len];
};

$fh = fopen($stlPath, 'wb');
fwrite($fh, str_pad('', 80));
fwrite($fh, pack('V', count($triangles)));
foreach ($triangles as $tri) {
    $n = $normal($tri[0], $tri[1], $tri[2]);
    $data = pack('f*', $n[0], $n[1], $n[2],
        $tri[0][0], $tri[0][1], $tri[0][2],
        $tri[1][0], $tri[1][1], $tri[1][2],
        $tri[2][0], $tri[2][1], $tri[2][2]);
    $data .= pack('v', 0);
    fwrite($fh, $data);
}
fclose($fh);

$sizeBytes = filesize($stlPath);

$dbPath = __DIR__ . '/../db/stl.sqlite';
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS jobs (
    id TEXT PRIMARY KEY,
    status TEXT,
    cache_key TEXT,
    download_url TEXT,
    triangles INTEGER,
    size_bytes INTEGER,
    created_at TEXT
)");
$stm = $db->prepare("INSERT OR REPLACE INTO jobs (id,status,cache_key,download_url,triangles,size_bytes,created_at) VALUES (:id,'done',:key,:url,:tri,:size,datetime('now'))");
$stm->execute([
    ':id' => $jobId,
    ':key' => $cacheKey,
    ':url' => $downloadUrl,
    ':tri' => count($triangles),
    ':size' => $sizeBytes
]);

echo json_encode([
    'ok' => true,
    'status' => 'done',
    'job_id' => $jobId,
    'download_url' => $downloadUrl,
    'meta' => [
        'triangles' => count($triangles),
        'format' => 'Binary',
        'size_bytes' => $sizeBytes
    ]
]);
