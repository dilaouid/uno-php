<?php

/* header('Content-Type: application/json'); */

include('config/global.php');
$Room = new \Uno\Room($DB, null);

$get = array_map("htmlspecialchars", $_GET);

foreach ($get as $key => $value) {
    switch ($key) {
        case 'getInfoRoom':

            $Room->checkRoom($value);
            $roomInfos = $Room->getInfos($value);
            $players = $Lib->decode($roomInfos['players'], 'players');

            if (!$Room->inRoom($roomInfos)) {
                $Room->sendError('403');
            }

            $roomInfos['admin'] == $_COOKIE['player'] ? $admin = true : $admin = false;
            for ($i=0; $i < count($players); $i++) { 
                unset($players[$i]->cards,
                      $players[$i]->cookie, 
                      $players[$i]->admin);
            } /* Supprime les informations sensibles de la réponse JSON */
            if ($roomInfos['active'] == 1) {
                $status = 2; // La partie peut commencer !!
            } else if (count($players) > 1) {
                $status = 1; // En attente de confirmation de l'admin
            } else {
                $status = 0; // En attente de joueur
            }
            echo json_encode([
                "name"      => $value,
                "players"   => $players,
                "status"    => $status,
                "admin"     => $admin,
                "online"    => count($players)
            ]);
            exit();
            break;
        case 'expulse':
            $value      = explode('_', $value);
            $roomInfos = $Room->checkRoom($value)->getInfos($value);
            $players    = $Lib->decode($roomInfos['players'], 'players');

            if ($roomInfos['admin'] == $_COOKIE['player']) {
                unset($players[$value[0]]);
                $roomPlayers = '{
                    "players": '
                            . json_encode($players) .
                    '}';
                $Lib->updateCol('uno_room', ['players', 'open'], "name = '$value[1]'", [ $roomPlayers, 1 ]); // Rouvre la room, et supprime le joueur expulsé
                echo 'true';
            } else {
                echo 'false';
            } // Si l'expulseur n'est pas admin, la page renvoie une erreur

            exit();
            break;
        case 'inRoom':

            $roomInfos = $Room->checkRoom($value)->getInfos($value);
            $players = $Lib->decode($roomInfos['players'], 'players');
            echo $Room->inRoom($roomInfos);
            exit();

            break;
        case 'startRoom':
            $Room->checkRoom($value);
            $roomInfos = $Room->getInfos($value);
            
            $players = $Lib->decode($roomInfos['players'], 'players');
            if ($roomInfos['admin'] == $_COOKIE['player'] && count($players) > 1) { // Seul l'admin peut lancer la partie
                $Lib->updateCol('uno_room', ['nb_players', 'open', 'active', 'turn', 'msg'], "name = '$value'", [ count($players), 0, 1, $players[0]->cookie, "C'est au tour de {$players[0]->username} !" ]);
                $Game = new \Uno\Game($DB, $value, $roomInfos);
                $Game->shuffle()->drawCard(9);
                echo 'true';
            } else {
                echo 'false';
            }
            exit();

            break;
        case 'getInfoGame':

            $roomInfos = $Room->checkRoom($value)->getInfos($value);
            $players = $Lib->decode($roomInfos['players'], 'players');
            
            if (!$Room->inRoom($roomInfos)) {
                $Room->sendError('403');
            }
            
            $hand       = [];
            $opponents  = [];
            $turn       = null;
            $lastCard   = $roomInfos['lastcard'];

            $yourTurn = $roomInfos['turn'] == $_COOKIE['player'];
            foreach ($players as $key => $value) {
                if ($value->cookie == $roomInfos['turn']) {
                    $turn = $value->id;
                }
                if ($value->cookie == $_COOKIE['player']) {
                    $hand = $value->cards;
                } else {
                    $opponents[$key] = count($value->cards);
                }
            }
            echo json_encode([
                "hand"      => $hand,
                "turn"      => $turn,
                "your_turn" => $yourTurn,
                "opponents" => $opponents,
                "lastcard"  => $lastCard,
                "uno"       => (boolean)$roomInfos['uno'],
                "nb"        => $roomInfos['nb'],
                "msg"       => $roomInfos['msg'],
                "effect"    => $roomInfos['effect']
            ]);
            exit();
            break;
        case 'playcard':
            $queryString = explode('%', $value);
            $roomID     = $queryString[0];
            $cardname   = urldecode($queryString[1]);

            $roomInfos = $Room->checkRoom($roomID)->getInfos($roomID);

            $players = $Lib->decode($roomInfos['players'], 'players');
            if (!$Room->inRoom($roomInfos)) {
                $Room->sendError('403');
            }
            
            if (in_array(substr($cardname, 0, 2), [' 2', ' 4'])) {
                $cardname = '+' . trim($cardname);
            }

            if (($roomInfos['turn'] != $_COOKIE['player'] || $roomInfos['effect'] == true)
            || !$Room->checkHand($cardname, $players) || $roomInfos['uno'] == 1) {
                echo 'false';
                exit();
            }
            $Game = new \Uno\Game($DB, $value, $roomInfos);
            $Game->playCard($cardname);
            break;

        case 'draw':
            $roomInfos  = $Room->checkRoom($value)->getInfos($value);
            $players    = $Lib->decode($roomInfos['players'], 'players');
            if (!$Room->inRoom($roomInfos)) {
                $Room->sendError('403');
            }
            $Room->checkTurn($roomInfos['turn']);
            $roomInfos['effect'] == 1 ? $drawCount = substr($roomInfos['lastcard'], 1, 2) : $drawCount = 1;
            $Game = new \Uno\Game($DB, $value, $roomInfos);
            echo $Game->drawCard($Game->getPlayerIndex(), $drawCount);
            $Game->updateTurnDB(null);
            break;
        default:
            echo 'Salut :-)'; // Easter egg
            break;
    }
}