<div class="row no-gutters justify-content-center" id="opponents">
    <!-- ICI LES ADVERSAIRES -->

    <?php

    foreach ($roomInfos['players'] as $key => $value) {
        if ($value->cookie != $_COOKIE['player']) {
            echo '<div class="col-1 pulse animated opp">
            <div class="row no-gutters" data-bs-hover-animate="pulse" style="margin-right: 75%;" id="hand_player_'.$value->id.'">
                <div class="col"><img src="assets/img/cards/back.png" class="handplayer"></div>
            </div>
            <div class="row no-gutters text-center text-white">
                <div class="col">
                    <p style="font-size: 22px;">
                        <strong>'.$value->username.'</strong>
                    </p>
                </div>
            </div>
            <div class="d-none row no-gutters text-center text-white ongoing" style="margin-top: -10px;" id="ongoing_'.$value->id.'">
                <div class="col abc"><span class="spinner-border text-warning " role="status"></span></div>
            </div>
            </div>';
        }
    }

    ?>

</div>

<div class="row no-gutters justify-content-center">
    <div class="col-auto align-self-center" style="position: absolute;filter: brightness(121%) contrast(113%) hue-rotate(0deg);opacity: 0.70;margin-left: -1205px;">
        <p>
            <strong id="msgInfo"><!-- ICI LE MESSAGE INFO --></strong>
        </p>
    </div>

    <div class="col-auto align-self-center pulse animated">
        <!-- ICI LA DERNIERE CARTE POSÉE -->
        <img    class="shadow-lg"
                data-bs-hover-animate="jello"
                src="assets/img/cards/back.png"
                id="lastcard"
        >
    </div>

    <div class="col-1 align-self-center pulse animated" style="margin-right: 70px;margin-left: 69px;">
        <div class="row no-gutters" style="margin-right: 75%;" id="deck">
            <!-- ICI LE DECK -->
            <div class="col"><img src="assets/img/cards/back.png" class="card-back"></div>
            <div class="col"><img src="assets/img/cards/back.png" class="card-back"></div>
            <div class="col"><img src="assets/img/cards/back.png" class="card-back"></div>
            <div class="col"><img src="assets/img/cards/back.png" class="card-back"></div>
        </div>
    </div>

    <div class="col-1 align-self-center" id="btnchoix">

        <div class="row no-gutters d-none" id="pioche" style="margin-right: 75%;">
            <div class="col">
                <!-- ICI LE BOUTON POUR PIOCHER -->
                <button class="btn btn-danger btn-lg btnRight btnPioche" data-bs-hover-animate="pulse" type="button" onclick="draw()">
                    <strong>PIOCHE <span id="nbDraw"></span></strong>
                </button>
            </div>
        </div>


            <div class="row no-gutters d-none" style="margin-right: 75%;" id="contreUno">
                <div class="col">
                    <!-- CONTRE UNO BTN -->
                    <button class="btn btn-danger btn-lg btnRight btnContreUno" data-bs-hover-animate="pulse" type="button" onclick="uno()">
                        <strong>CONTRE UNO</strong>
                    </button>
                </div>
            </div>
        

            <div class="row no-gutters d-none" style="margin-right: 75%;" id="Uno">
                <div class="col">
                    <!-- UNO BTN -->
                    <button class="btn btn-danger btn-lg btnRight btnUno" data-bs-hover-animate="pulse" type="button" onclick="uno()">
                        <strong>UNO</strong>
                    </button>
                </div>
            </div>

        <div class="row no-gutters d-none" style="margin-right: 75%;" name="pickcolor">
            <div class="col">
                <button 
                    class="btn btn-danger btn-lg"
                    data-bs-hover-animate="pulse"
                    type="button"
                    style="width: 309px;background: url(assets/img/modalHeader.png) center / cover no-repeat;">
                        <strong>Rouge</strong>
                </button>
            </div>
        </div>

        <div class="row no-gutters d-none" style="margin-right: 75%;" name="pickcolor">
            <div class="col">
                <button 
                    class="btn btn-success btn-lg"
                    data-bs-hover-animate="pulse"
                    type="button"
                    style="width: 309px;background: url(assets/img/greenBtn.png) center / cover;">
                    <strong>Vert</strong>
                </button>
            </div>
        </div>

        <div class="row no-gutters d-none" style="margin-right: 75%;" name="pickcolor">
            <div class="col">
                <button 
                    class="btn btn-warning btn-lg text-white"
                    data-bs-hover-animate="pulse"
                    type="button"
                    style="width: 309px;background: url(assets/img/yellowBtn.png) center / cover;">
                    <strong>Jaune</strong>
                </button>
            </div>
        </div>

        <div class="row no-gutters d-none" style="margin-right: 75%;" name="pickcolor">
            <div class="col">
                <button 
                    class="btn btn-info btn-lg"
                    data-bs-hover-animate="pulse"
                    type="button"
                    style="width: 309px;background: url(assets/img/buttonJoin.png) center / cover no-repeat;">
                    <strong>Bleu</strong>
                </button>
            </div>
        </div>

    </div>

</div>

<div class="row no-gutters justify-content-center" id="rowPlayer">
<!--     <div class="col-auto align-self-center" id="cardHand">
            <img class="shadow-lg card-hand" src="assets/img/cards/red/1.png" onclick="play('red_1')">
    </div> -->
</div>