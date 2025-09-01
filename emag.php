<?php
require_once __DIR__ . '/emag/SimpleXLSX.php';
use Shuchkin\SimpleXLSX;

$dir = __DIR__ . '/emag';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}
$uploadDir = $dir . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$dbPath = $dir . '/emag.db';
$initDb = !file_exists($dbPath);
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ($initDb) {
    $pdo->exec('CREATE TABLE uploads (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, original_name TEXT, type TEXT, uploaded_at INTEGER)');
}

$previews = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['files'])) {
    $fileCount = count($_FILES['files']['name']);
    if ($fileCount > 10) {
        $error = 'Se pot incarca maxim 10 fisiere.';
    } else {
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                $tmp  = $_FILES['files']['tmp_name'][$i];
                $name = $_FILES['files']['name'][$i];
                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['xlsx', 'pdf', 'zip'])) {
                    $safeName = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
                    $dest = $uploadDir . '/' . $safeName;
                    move_uploaded_file($tmp, $dest);

                    $stmt = $pdo->prepare('INSERT INTO uploads(filename, original_name, type, uploaded_at) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$safeName, $name, $ext, time()]);

                    if ($ext === 'xlsx') {
                        if ($xlsx = SimpleXLSX::parse($dest)) {
                            $rows = $xlsx->rows();
                            $html = '<table class="w-full text-sm text-left border border-gray-300 dark:border-gray-600">';
                            if (!empty($rows)) {
                                $html .= '<thead class="bg-gray-100 dark:bg-gray-700"><tr>';
                                foreach ($rows[0] as $cell) {
                                    $html .= '<th class="border border-gray-300 dark:border-gray-600 px-2 py-1">' . htmlspecialchars($cell) . '</th>';
                                }
                                $html .= '</tr></thead><tbody>';
                                $limit = min(10, count($rows) - 1);
                                for ($r = 1; $r <= $limit; $r++) {
                                    $html .= '<tr>';
                                    foreach ($rows[$r] as $cell) {
                                        $html .= '<td class="border border-gray-300 dark:border-gray-600 px-2 py-1">' . htmlspecialchars($cell) . '</td>';
                                    }
                                    $html .= '</tr>';
                                }
                                $html .= '</tbody>';
                            }
                            $html .= '</table>';
                            $previews[] = ['type' => 'xlsx', 'name' => $name, 'table' => $html];
                        }
                    } elseif ($ext === 'pdf') {
                        $previews[] = ['type' => 'pdf', 'name' => $name, 'url' => 'emag/uploads/' . $safeName];
                    } elseif ($ext === 'zip') {
                        $zip = new ZipArchive();
                        if ($zip->open($dest) === true) {
                            for ($j = 0; $j < $zip->numFiles; $j++) {
                                $zipName = $zip->getNameIndex($j);
                                $zext = strtolower(pathinfo($zipName, PATHINFO_EXTENSION));
                                if ($zext === 'pdf') {
                                    $stream = $zip->getStream($zipName);
                                    if ($stream) {
                                        $contents = stream_get_contents($stream);
                                        fclose($stream);
                                        $innerSafe = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $zipName);
                                        $innerDest = $uploadDir . '/' . $innerSafe;
                                        file_put_contents($innerDest, $contents);
                                        $stmt->execute([$innerSafe, $zipName, 'pdf', time()]);
                                        $previews[] = ['type' => 'pdf', 'name' => $zipName, 'url' => 'emag/uploads/' . $innerSafe];
                                    }
                                }
                            }
                            $zip->close();
                        }
                    }
                } else {
                    $error = 'Tip de fisier neacceptat: ' . htmlspecialchars($name);
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="ro" x-data="{dark: window.matchMedia('(prefers-color-scheme: dark)').matches}" x-bind:class="{ 'dark': dark }">
<head>
<meta charset="utf-8"/>
<title>eMAG Upload</title>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@^3/dist/tailwind.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="font-sans bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors">
<div class="max-w-5xl mx-auto p-6 space-y-6">
<div class="flex justify-end">
<button @click="dark = !dark" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition" aria-label="Toggle dark mode">
<span x-show="!dark">🌙</span>
<span x-show="dark">☀️</span>
</button>
</div>
<form method="post" enctype="multipart/form-data" class="space-y-4">
<div id="drop_zone" class="flex flex-col items-center justify-center p-10 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-center cursor-pointer bg-white/60 dark:bg-gray-800/40 transition-colors">
<p class="text-gray-600 dark:text-gray-300">Drag & Drop (xlsx, pdf, zip) sau click pentru selectie. Maxim 10 fisiere.</p>
<input type="file" id="fileInput" name="files[]" multiple accept=".xlsx,.pdf,.zip" class="hidden"/>
</div>
<button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">Incarca</button>
</form>
<?php if (!empty($error)): ?>
<div class="mt-4 text-red-500"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($previews)): ?>
<div class="mt-8 grid gap-6 sm:grid-cols-2">
<?php foreach ($previews as $p): ?>
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
<h2 class="text-lg font-semibold mb-3"><?= htmlspecialchars($p['name']) ?></h2>
<?php if ($p['type'] === 'xlsx'): ?>
<div class="overflow-x-auto"><?= $p['table'] ?></div>
<?php else: ?>
<embed src="<?= htmlspecialchars($p['url']) ?>#toolbar=0&page=1" type="application/pdf" class="w-full h-64 border rounded"/>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
<script>
const dropZone = document.getElementById('drop_zone');
const fileInput = document.getElementById('fileInput');
dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', e => {
  e.preventDefault();
  dropZone.classList.add('bg-blue-50','dark:bg-gray-700');
});
dropZone.addEventListener('dragleave', () => {
  dropZone.classList.remove('bg-blue-50','dark:bg-gray-700');
});
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  dropZone.classList.remove('bg-blue-50','dark:bg-gray-700');
  const files = e.dataTransfer.files;
  if (files.length > 10) { alert('Maxim 10 fisiere'); return; }
  const dt = new DataTransfer();
  for (let i = 0; i < files.length && i < 10; i++) {
    dt.items.add(files[i]);
  }
  fileInput.files = dt.files;
});
</script>
</body>
</html>
