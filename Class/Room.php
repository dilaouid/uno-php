<?php

namespace Uno;
use \Uno\Lib;

class Room extends Lib {

    private $roomID;
    private $db;
    public $deck = '{
        "red": [ "0", "1", "1", "2", "2", "3", "3", "4", "4", "5", "5", "6", "6", "7", "7", "8", "8", "9", "9", "+2", "+2", "r", "r", "p", "p" ],
        "blue": [ "0", "1", "1", "2", "2", "3", "3", "4", "4", "5", "5", "6", "6", "7", "7", "8", "8", "9", "9", "+2", "+2", "r", "r", "p", "p" ],
        "yellow": [ "0", "1", "1", "2", "2", "3", "3", "4", "4", "5", "5", "6", "6", "7", "7", "8", "8", "9", "9", "+2", "+2", "r", "r", "p", "p" ],
        "green": [ "0", "1", "1", "2", "2", "3", "3", "4", "4", "5", "5", "6", "6", "7", "7", "8", "8", "9", "9", "+2", "+2", "r", "r", "p", "p" ],
        "joker": [ "n", "n", "n", "n", "+4", "+4", "+4", "+4" ]
    }';
    public $players;

    /**
    * Sélectionne des valeurs dans la table choisies
    *
    * @param obj $db Objet de la base de données
    * @param str $roomID Identifiant de la room
    *
    */
	public function __construct($db, $roomID) {
        parent::__construct($db);
        if ($roomID) {
            $this->roomID = $roomID;
        }
        $this->db = $db;
    }

    /**
    * Retourne le nombre de salons accessibles
    *
    * @return number
    */
    public function getNbRooms() {
        $nbRooms = $this->countOcc('uno_room', 'id', 'active = 1');
        if ($nbRooms == 0) {
            return '0 PARTIE EN COURS :-(';
        } else if ($nbRooms == 1) {
            return $nbRooms . ' PARTIE EN COURS';
        } else {
            return $nbRooms . ' PARTIES EN COURS';
        }
    }

    /**
    * Retourne si un salon est accessible ou non
    *
    * @param str $id Identifiant de la room
    *
    * @return boolean
    */
    public function checkValidRoom($id) {
        return $this->selectSQL('uno_room', 'open', ["name"], [ $id ])['open'];
    }

    /**
    * Retourne si un salon est correctement formatté et existant ou non
    *
    * @param str $roomID Identifiant de la room
    *
    * @return boolean
    */
    public function checkRoom($roomID) {
        if (strlen($roomID) != 13 || !$this->checkExistsRoom($roomID)) {
            $this->sendError('404');
        }
        return $this;
    }


    /**
    * Retourne si un salon existe ou non
    *
    * @param str $id Identifiant de la room
    *
    * @return boolean
    */
    public function checkExistsRoom($id) {
        return $this->countOcc('uno_room', 'id', "name = '$id'");
    }

    /**
    * Crée une room
    *
    * @param str $id Identifiant de la room
    * @param number $nbplayers Nombre de joueurs de la room
    * @param str Identifiant du créateur de la room
    *
    *
    */
    public function createRoom($id, $nbplayers, $username) {
        $this->players = '{
            "players": [
                {
                    "id": 0,
                    "cookie": "'. $_COOKIE['player'] .'",
                    "username": "'. $username .'",
                    "cards": [ null ],
                    "admin": true
                }
            ]
        }';
        $this->insertSQL("uno_room", 'name, admin, nb_players', [$id, $_COOKIE['player'], $nbplayers]);
        $this->db->query("UPDATE uno_room SET players = '$this->players', deck = '$this->deck' WHERE name = '$id'");
    }

    /**
    * Retourne les infos de la room
    *
    * @param str $id Identifiant de la room
    * @return array 
    *
    */
    public function getInfos($id) {
        return $this->selectAndFetch('uno_room', '*', "name = '{$id}'", ['1']);
    }

    /**
    * Rejoindre une room
    *
    * @param str $id Identifiant de la room 
    * @param str $username Identifiant de l'utilisateur qui rejoint la room
    *
    */
    public function joinRoom($id, $username) {
        $roomURL = 'Location: room.php?id=' . $id;
        if (!$this->checkExistsRoom($id)) { return header('Location: .'); }
        $roomInfos = $this->getInfos($id);
        if ($this->inRoom($roomInfos)) { return header($roomURL); }
        $roomInfos['players'] = $this->decode($roomInfos['players'], 'players');
        $roomInfos['players'][count($roomInfos['players'])] = 
        [
            "id" => count($roomInfos['players']),
            "cookie" => $_COOKIE['player'],
            "username" => $username,
            "cards" => [null],
            "admin" => false
        ];
        $roomPlayers = '{
            "players": '
                    . json_encode($roomInfos['players']) .
            '}';
        $roomInfos['nb_players'] == count($roomInfos['players']) ? $open = 0 : $open = 1;
        $this->updateCol('uno_room', ['players', 'open'], "name = '$id'", [ $roomPlayers, $open ], false);
        header($roomURL);
    }

    /**
    * Vérifie si le visiteur est inscrit dans la room
    *
    * @return boolean
    *
    */
    public function inRoom($roomInfos = null) {
        if ($roomInfos == null) {
            $roomInfos = $this->getInfos($this->roomID);
        }
        $roomInfos['players'] = $this->decode($roomInfos['players'], 'players');
        foreach ($roomInfos['players'] as $key => $value) {
            if ($value->cookie == $_COOKIE['player']) {
                return TRUE;
            }
        }
        return FALSE; 
    }

    /**
    * Vérifie si la partie est en cours
    *
    * @return boolean
    *
    */
    public function onPlay()
    {
        return $this->getInfos($this->roomID)['active'];
    }

    /**
    * Vérifie si un joueur a bien une carte en main
    *
    * @param str $card Nom de la carte
    * @param arr $players Informations des joueurs la room TRAFALGAR LAW ROOM
    *
    * @return boolean
    *
    */
    public function checkHand($card, $players)
    {
        $correctCardName=  explode(',', $card)[0];
        foreach ($players as $key => $value) {
            if ($value->cookie == $_COOKIE['player'] && in_array($correctCardName, $value->cards)) {
                return true;
            }
        }
        return false;
    }


    /**
    * Renvoie une erreur et bloque l'accès à la page
    *
    * @return null
    *
    */
    public function sendError($httpcode)
    {
        $httpcode == '404' ? $header = 'HTTP/1.0 403 Forbidden' : $header = 'HTTP/1.0 404 Not Found';
        header($header, true, $httpcode);
        exit();
    }


    /**
    * Vérifie si c'est bien au tour du joueur dont les cookies sont stockés en paramètre
    *
    * @param str $turnInfos Les infos du tour stockés dans la roomInfos
    *
    * @return boolean
    *
    */
    public function checkTurn($turnInfos)
    {
        if ($turnInfos != $_COOKIE['player']) {
            echo 'false';
            exit();
        }
    }

}