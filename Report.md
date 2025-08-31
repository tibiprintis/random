# Report

## Summary
- Added support for complex SVG paths (cubic and quadratic curves, smooth commands) with curve subdivision driven by the user-selected detail level.
- Reworked interface controls to expose numeric segment detail and preview rotation speed.
- Enabled preview customization through adjustable rotation speed.

## Code Snippets
### stl/api/convert.php
**Before**
```php
} elseif (isset($svg->path[0])) {
    $d = (string)$svg->path[0]['d'];
    preg_match_all('/[ML]\s*([\d\.\-]+)[, ]([\d\.\-]+)/i', $d, $m, PREG_SET_ORDER);
    foreach ($m as $seg) {
        $points[] = [floatval($seg[1]), floatval($seg[2])];
    }
}
```
**After**
```php
} elseif (isset($svg->path[0])) {
    $d = (string)$svg->path[0]['d'];
    $points = pathToPoints($d, max(4, $params['segments']));
}
```

### stl/js/ui.js
**Before**
```javascript
<select id="detail">
    <option>Low</option>
    <option selected>Medium</option>
    <option>High</option>
</select>
<input type="number" id="offset" placeholder="Offset mm" value="0">
```
**After**
```javascript
<input type="number" id="segments" placeholder="Segments" value="32" min="3">
<input type="number" id="offset" placeholder="Offset mm" value="0">
<input type="number" id="rotation" placeholder="Rotation speed" value="0.01" step="0.01">
```

### stl/js/preview.js
**Before**
```javascript
if (this.mesh) {
    this.mesh.rotation.y += 0.01;
}
```
**After**
```javascript
this.rotationSpeed = 0.01;
if (this.mesh) {
    this.mesh.rotation.y += this.rotationSpeed;
}
...
setRotationSpeed(speed) {
    this.rotationSpeed = speed;
}
```

