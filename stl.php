<?php
session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVG to STL Converter</title>
    <link rel="stylesheet" href="stl/css/styles.css">
</head>
<body>
    <div id="controls"></div>
    <div id="notifications"></div>
    <div id="preview"></div>
    <div id="download"></div>
    <script>
    window.APP_CONFIG = {
        api: {
            convert: 'stl/api/convert.php',
            status: 'stl/api/status.php',
            download: 'stl/api/download.php'
        },
        csrf: '<?php echo $csrf; ?>'
    };
    </script>
    <script src="stl/vendor/three.min.js"></script>
    <script src="stl/js/utils.js"></script>
    <script src="stl/js/svgParser.js"></script>
    <script src="stl/js/revolve.js"></script>
    <script src="stl/js/stlExporter.js"></script>
    <script src="stl/js/preview.js"></script>
    <script src="stl/js/ui.js"></script>
    <script src="stl/js/main.js"></script>
</body>
</html>
