<?php
require('functions.php');
header("Content-Type: application/manifest+json");
header('Cache-Control: private, max-age=0');
$baseUrl = "https://" . $_SERVER['HTTP_HOST'] . "/";
?>{
"dir": "ltr",
"lang": "English",
"name": "<?= getSiteTitle() ?>",
"scope": "/",
"display": "standalone",
"start_url": "<?= $baseUrl ?>?pwa=true",
"short_name": "<?= getSiteTitle() ?>",
"theme_color": "#0c1d2d",
"description": "<?= getSiteTitle() ?> now playing, next playing, weather, import iCal ICS Google Calendar Outlook",
"orientation": "any",
"background_color": "#0c1d2d",
"related_applications": [],
"prefer_related_applications": false,
"icons": [
{
"src": "<?= cacheBusterLink("img/pwa/icon-72x72.png") ?>",
"sizes": "72x72",
"type": "image/png"
},
{
"src": "<?= cacheBusterLink("img/pwa/icon-96x96.png") ?>",
"sizes": "96x96",
"type": "image/png"
},
{
"src": "<?= cacheBusterLink("img/pwa/icon-128x128.png") ?>",
"sizes": "128x128",
"type": "image/png"
},
{
"src": "<?= cacheBusterLink("img/pwa/icon-144x144.png") ?>",
"sizes": "144x144",
"type": "image/png"
},
{
"src": "<?= cacheBusterLink("img/pwa/icon-152x152.png") ?>",
"sizes": "152x152",
"type": "image/png"
},
{
"src": "<?= cacheBusterLink("img/pwa/icon-192x192.png") ?>",
"sizes": "192x192",
"type": "image/png"
},
{
"src": "<?= cacheBusterLink("img/pwa/icon-384x384.png") ?>",
"sizes": "384x384",
"type": "image/png"
},
{
"src": "<?= cacheBusterLink("img/pwa/icon-512x512.png") ?>",
"sizes": "512x512",
"type": "image/png"
}
]
}