<?php

/* header('Content-Type: application/json'); */

include('config/global.php');
$Room = new \Uno\Room($DB, null);

$get = array_map("htmlspecialchars", $_GET);

foreach ($get as $key => $value) {
    switch ($key) {
        case 'getInfoRoom':
            if (strlen($value) != 13 || !$Room->checkExistsRoom($value)) {
                echo '403';
                exit();
            } /* Si la room est invalide, retourner une erreur 403 */
            $roomInfos = $Room->getInfos($value);
            $players = $Lib->decode($roomInfos['players'], 'players');

            if (!$Room->inRoom($roomInfos)) {
                echo '403';
                exit();
            }

            $roomInfos['admin'] == $_COOKIE['player'] ? $admin = true : $admin = false;
            for ($i=0; $i < count($players); $i++) { 
                unset($players[$i]->cards);
                unset($players[$i]->cookie);
                unset($players[$i]->admin);
            } /* Supprime les informations sensibles de la réponse JSON */
            if ($roomInfos['active'] == 1) {
                $status = 2; // La partie peut commencer !!
            } else if (count($players) > 1) {
                $status = 1; // En attente de confirmation de l'admin
            } else {
                $status = 0; // En attente de joueur
            }
            $data = [
                "name" => $value,
                "players" => $players,
                "status" => $status,
                "admin" => $admin,
                "online" => count($players)
            ];
            echo json_encode($data);
            exit();
            break;
        case 'expulse':
            $value = explode('_', $value);

            if (strlen($value[1]) != 13 || !$Room->checkExistsRoom($value[1])) {
                echo '403';
                exit();
            } /* Si la room est invalide, retourner une erreur 403 */

            $roomInfos = $Room->getInfos($value[1]);
            $players = $Lib->decode($roomInfos['players'], 'players');

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
            if (strlen($value) != 13 || !$Room->checkExistsRoom($value)) {
                echo '403';
                exit();
            } /* Si la room est invalide, retourner une erreur 403 */

            $roomInfos = $Room->getInfos($value);
            $players = $Lib->decode($roomInfos['players'], 'players');
            $ret = $Room->inRoom($roomInfos);
            echo $ret;
            exit();

            break;
        case 'startRoom':
            if (strlen($value) != 13 || !$Room->checkExistsRoom($value)) {
                echo '403';
                exit();
            } /* Si la room est invalide, retourner une erreur 403 */

            $roomInfos = $Room->getInfos($value);
            $players = $Lib->decode($roomInfos['players'], 'players');
            if ($roomInfos['admin'] == $_COOKIE['player'] && count($players) > 1) { // Seul l'admin peut lancer la partie
                $Lib->updateCol('uno_room', ['nb_players', 'open', 'active', 'turn', 'msg'], "name = '$value'", [ count($players), 0, 1, $players[0]->cookie, "C'est au tour de {$players[0]->username} !" ]);
                $Game = new \Uno\Game($DB, $value, $roomInfos);
                $Game->shuffle();
                $Game->drawCard(9);
                echo 'true';
            } else {
                echo 'false';
            }

            exit();
            break;
        case 'getInfoGame':
            if (strlen($value) != 13 || !$Room->checkExistsRoom($value)) {
                echo '403';
                exit();
            } /* Si la room est invalide, retourner une erreur 403 */

            $roomInfos = $Room->getInfos($value);
            $players = $Lib->decode($roomInfos['players'], 'players');
            if (!$Room->inRoom($roomInfos)) {
                echo '403';
                exit();
            } // Si le visiteur n'est pas dans la room, byebye

            $hand       = [];
            $opponents  = [];
            $turn       = null;
            $lastCard   = $roomInfos['lastcard'];

            $roomInfos['turn'] == $_COOKIE['player'] ? $yourTurn = true : $yourTurn = false;
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
            $data = [
                "hand"      => $hand,
                "turn"      => $turn,
                "your_turn" => $yourTurn,
                "opponents" => $opponents,
                "lastcard"  => $lastCard,
                "uno"       => (boolean)$roomInfos['uno'],
                "nb"        => $roomInfos['nb'],
                "msg"       => $roomInfos['msg'],
                "effect"    => $roomInfos['effect']
            ];
            echo json_encode($data);
            exit();
            break;
        case 'playcard':
            $queryString = explode('%', $value);
            $roomID = $queryString[0];
            $cardname = $queryString[1];

            if (strlen($roomID) != 13 || !$Room->checkExistsRoom($roomID)) {
                echo '403';
                exit();
            } /* Si la room est invalide, retourner une erreur 403 */

            $cardname = urldecode($cardname);

            $roomInfos = $Room->getInfos($roomID);
            $players = $Lib->decode($roomInfos['players'], 'players');
            if (!$Room->inRoom($roomInfos)) {
                echo '403';
                exit();
            } // Si le visiteur n'est pas dans la room, byebye

            if (in_array(substr($cardname, 0, 2), [' 2', ' 4'])) {
                $cardname = '+' . trim($cardname);
            }

            if (($roomInfos['turn'] != $_COOKIE['player'] || $roomInfos['effect'] == true)
            || !$Room->checkHand($cardname, $players) || $roomInfos['uno'] == 1) {
                echo 'false';
                exit();
            }

            $Game = new \Uno\Game($DB, $value, $roomInfos);
            echo $Game->playCard($cardname);

            break;
        case 'draw':
            if (strlen($value) != 13 || !$Room->checkExistsRoom($value)) {
                echo '403';
                exit();
            } /* Si la room est invalide, retourner une erreur 403 */

            $roomInfos = $Room->getInfos($value);
            $players = $Lib->decode($roomInfos['players'], 'players');

            if (!$Room->inRoom($roomInfos)) {
                echo '403';
                exit();
            } // Si le visiteur n'est pas dans la room, byebye

            if ($roomInfos['turn'] != $_COOKIE['player']) {
                echo 'false';
                exit();
            }

            $drawCount = 1;

            if ($roomInfos['effect'] == 1) {
                $drawCount = substr($roomInfos['lastcard'], 1, 2);
            }
            $Game = new \Uno\Game($DB, $value, $roomInfos);
            $index = $Game->getPlayerIndex();

            $Game->drawCard($index, $drawCount);
            $Game->updateTurnDB(null);

            echo 'true';

            break;
        default:
            echo 'Salut :-)'; // Easter egg
            break;
    }
}