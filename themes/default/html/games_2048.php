<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $C->SITE_TITLE ?>-Games-2048</title>

    <link href="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/css/main.css" rel="stylesheet" type="text/css">

    <link rel="apple-touch-icon" href="meta/apple-touch-icon.png">
    <link rel="apple-touch-startup-image" href="meta/apple-touch-startup-image-640x1096.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)"> <!-- iPhone 5+ -->
    <link rel="apple-touch-startup-image" href="meta/apple-touch-startup-image-640x920.png"  media="(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)"> <!-- iPhone, retina -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    <?php if( isset($D->page_favicon) ) { ?>
        <link href="<?= $D->page_favicon ?>" type="image/x-icon" rel="shortcut icon" />
    <?php } elseif( $C->HDR_SHOW_FAVICON == 1 ) { ?>
        <link href="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/favicon.ico ?>" type="image/x-icon" rel="shortcut icon" />
    <?php } elseif( $C->HDR_SHOW_FAVICON == 2 ) { ?>
        <link href="<?= $C->IMG_URL.'attachments/'.$this->network->id.'/'.$C->HDR_CUSTOM_FAVICON ?>" type="image/x-icon" rel="shortcut icon" />
    <?php } ?>
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <meta name="viewport" content="width=device-width, target-densitydpi=160dpi, initial-scale=1.0, maximum-scale=1, user-scalable=no, minimal-ui">
</head>
<body>
<div class="container">
    <div class="heading">
        <h1 class="title" style="margin-top:-20px;">2048</h1>
        <div class="scores-container">
            <div class="score-container">0</div>
            <div class="best-container">0</div>

        </div>
    </div>
    <div class="above-game">
        <p class="game-intro">پیش به سوی ساخت کاشی 2048</p>
        <a class="restart-button">بازی جدید</a>
    </div>

    <div class="game-container">
        <div class="game-message">
            <p></p>
            <div class="lower">
                <a class="keep-playing-button">ادامه</a>
                <a class="retry-button">بازی مجدد</a>
            </div>
        </div>

        <div class="grid-container">
            <div class="grid-row">
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
            </div>
            <div class="grid-row">
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
            </div>
            <div class="grid-row">
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
            </div>
            <div class="grid-row">
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
                <div class="grid-cell"></div>
            </div>
        </div>

        <div class="tile-container">

        </div>
    </div>

    <p class="game-explanation" style="direction: rtl">
        <strong class="important">نحوه بازی:</strong>با استفاده از <strong>دکمه های جهت نما</strong>خانه ها را حرکت دهید و آنها را با هم یکی کنید
    </p>
    <hr>
</div>

<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/bind_polyfill.js"></script>
<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/classlist_polyfill.js"></script>
<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/animframe_polyfill.js"></script>
<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/keyboard_input_manager.js"></script>
<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/html_actuator.js"></script>
<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/grid.js"></script>
<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/tile.js"></script>
<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/local_storage_manager.js"></script>
<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/game_manager.js"></script>
<script src="<?= $C->SITE_URL .'themes/'.$C->THEME ?>/games/2048/js/application.js"></script>
</body>
</html>