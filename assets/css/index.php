<?php

include('config/global.php');

$RoomInfos = new \Uno\Room($DB, null);

$post = array_map("htmlspecialchars", $_POST);
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
if (isset($post['create'])) {
    if (strlen(trim($post['username'])) < 3) {
        $alert['createRoom'] = $Lib->createAlert("Votre nom d'utilisateur est trop court", 'danger');
    } else if (!is_numeric($post["players"]) || ($post['players'] < 2 || $post['players'] > 8)) {
        $alert['createRoom'] = $Lib->createAlert("Le nombre de joueurs autorisés est invalide", 'danger');
    } else {
        $roomID = uniqid();
        $RoomInfos->createRoom($roomID, $post['players'], $post['username']);
        header('Location: room.php?id=' . $roomID);
    }
    echo '<script>$(document).ready(function(){$("#newRoom").modal("show")});</script>';
} else if (isset($post['join'])) {
    if (strlen(trim($post['username'])) < 3) {
        $alert['joinRoom'] = $Lib->createAlert("Votre nom d'utilisateur est trop court", 'danger');
    } else if (strlen(trim($post['id'])) != 13) {
        $alert['joinRoom'] = $Lib->createAlert("L'id du salon doit faire 13 caractères", 'danger');
    } else if ($RoomInfos->checkValidRoom($post['id']) == false) {
        $alert['joinRoom'] = $Lib->createAlert("Ce salon ne peut plus être rejoint", 'danger');
    } else {
        $RoomInfos->joinRoom($post['id'], $post['username']);
    }
    echo '<script>$(document).ready(function(){$("#joinRoom").modal("show")});</script>';
}

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
    <link rel="icon" href="favicon.ico" />
</head>

<body style="background: url(assets/img/uno_bg.png);">
    <div class="row no-gutters text-center d-flex justify-content-center align-items-xl-center" style="margin-top: 15vh;">
        <div class="col-4" data-bs-hover-animate="pulse">
            <img class="img-fluid justify-content-xl-center align-items-xl-center tada animated" src="assets/img/Uno_logo.png">
        </div>
    </div>

    <div class="row no-gutters d-xl-flex justify-content-center" style="margin-left: 49px;opacity: 0.80;">
        <div class="col-auto" data-bs-hover-animate="pulse" style="margin-right: 47px;">
            <button class="btn btn-danger shadow" type="button" style="background: url(assets/img/buttonNew.png) center / contain round;width: 217px;height: 63px;"
            data-target="#newRoom" id="newRoomBtn">
                <strong>Créer un salon</strong>
            </button>
        </div>

        <div class="col-auto" data-bs-hover-animate="pulse" style="margin-right: 47px;">
            <button class="btn btn-primary shadow" type="button" style="background: url(assets/img/buttonJoin.png) center / auto round;width: 217px;height: 63px;"
            data-target="#joinRoom" id="joinRoomBtn">
                <strong>Rejoindre un salon</strong>
            </button>
        </div>
    </div>

    <div class="row no-gutters d-xl-flex justify-content-center" style="margin-left: 49px;opacity: 0.80;">
        <div class="col-auto" style="margin-right: 47px;">
            <p class="text-uppercase" style="font-size: 32px;color: rgb(255,255,255);text-align: center;opacity: 0.66;margin-top: 21px;text-shadow: 5px 4px 10px rgb(0,0,0);">
                <strong><?= $RoomInfos->getNbRooms(); ?></strong>
            </p>
        </div>
    </div>

    <?php include('inc/modals/createRoom.php'); ?>

    <?php include('inc/modals/joinRoom.php'); ?>

    <footer class="d-table-cell float-right d-xl-flex justify-content-xl-end" style="position: absolute;bottom: 0;right: 0;margin: 38px;">
        <div class="container">
            <div class="row">
                <div class="col-auto text-right d-xl-flex justify-content-xl-end">
                    <i class="fa fa-volume-off" data-bs-hover-animate="pulse" style="font-size: 65px;opacity: 0.67;color: rgb(255,255,255);" id="volume" onclick="volumeOn()"></i>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.min.js"></script>
    <script src="assets/js/index.js"></script>

</body>

</html>