<?php

include('config/global.php');

if (!isset($_GET['id'])) {
    header('Location: .');
}


$roomID = htmlspecialchars($_GET['id']);

$Room = new \Uno\Room($DB, $roomID);

if (!$Room->checkExistsRoom($roomID)) {
    header('Location: .');
}

$roomInfos = $Room->getInfos($roomID);

if (!$Room->inRoom($roomInfos)) {
    header('Location: .');
}

$roomInfos['players'] = $Lib->decode($roomInfos['players'], 'players');
$page = file_get_contents($URL . 'action.php?getInfoRoom=' . $roomID);

$roomInfos['admin'] == $_COOKIE['player'] ? $admin = true : $admin = false;

$onPlay = $Room->onPlay();

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Uno</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="assets/css/uno.css">
    <link rel="icon" href="favicon.ico" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <?php
    if (!$onPlay) { echo '<script>$(document).ready(function(){$("#players").modal("show")});</script>'; }
    ?>
</head>

<body style="background: url(assets/img/uno_bg.png);">

<?php

    if (!$onPlay) {
        include('inc/misc/logoTop.php');
        include('inc/modals/playersList.php');
    } else {
        include('inc/screen/mainGame.php');
    }
    
?>

    <footer class="d-table-cell float-right d-xl-flex justify-content-xl-end" style="position: absolute;bottom: 0;right: 0;margin: 38px;">
        <div class="container">
            <div class="row">
            <i id="volume" class="fa fa-volume-off" data-bs-hover-animate="pulse" style="font-size: 65px;opacity: 0.67;color: rgb(255,255,255);" id="volume" onclick="volumeOn()"></i>
            </div>
        </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.min.js"></script>
    <script src="assets/js/sound.js"></script>

    <?php

    if (!$onPlay) {
        echo '<script src="assets/js/screenPlayers.js"></script>';
    } else {
        echo '<script src="assets/js/game.js"></script>';
    }

    ?>

    <script>

    

    </script>
</body>

</html>