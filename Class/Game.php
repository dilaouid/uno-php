<?php

namespace Uno;

use \Uno\Lib;
use \Uno\Room;

class Game extends Room {
    private $roomID;
    public  $deck;
    public  $players;
    public  $lastcard;
    public  $pile = [];
    private $roomInfos;
    private $activePlayers = [];

    public function __construct($db, $roomID, $roomInfos)
    {
        $this->db           = $db;
        $this->roomID       = $roomInfos['name'];
        $this->roomInfos    = $roomInfos;

        parent::__construct($db, $roomID);

        $this->players      = $this->decode($this->roomInfos['players'], 'players');

        $this->deck         = htmlspecialchars_decode($this->roomInfos['deck']);
        $this->deck         = (array) json_decode($this->deck);

        $this->pile         = (array) json_decode($this->roomInfos['pile']);

    }

    
    /**
    * Distribue les cartes
    *
    */
    public function shuffle()
    {   
        foreach ($this->players as $key => $value) {
            unset($this->players[$key]->cards);
            for ($i = 0; $i < 7; $i++) {
                $this->drawCard($key);
            }
        }

    }

    
    /**
    * Pioche une carte du deck
    *
    * @param number $playerid ID du joueur
    * @param number $nb Nombre de cartes à piocher
    *
    */
    public function drawCard($playerid, $nb = 1)
    {
        for ($i=0; $i < $nb; $i++) {
            $stack      = $this->randomStack($playerid);
            $stackDeck  = (array) $this->deck[$stack];
            $card       = $stackDeck[array_rand($stackDeck, 1)] . '_' . $stack;
            if ($playerid < 9) {
                $this->players[$playerid]->cards[] = $card;
            } else {
                array_push($this->pile, $card);
                $this->setLastcard($card);
            }
            $this->removeFromDeck(explode('_', $card)[0], $stack);
        }
        $this->roomInfos['effect'] = 0;
        $this->updateDrawCard();
    }

    /**
    * Retire une carte du deck et mets la DB à jour
    *
    */
    private function removeFromDeck($deckplayer, $stack)
    {
        foreach ($this->deck[$stack] as $key => $value) {
            if ($value == $deckplayer) {
                unset($this->deck[$stack][$key]);
                $this->deck[$stack] = array_values($this->deck[$stack]);
                return true;
            }
        }
    }

    
    /**
    * 
    * Remplit le deck de la pile et mets la DB à jour
    *
    */
    private function reShuffle()
    {
        foreach ($this->pile as $key => $value) {
            $value = explode('_', $value);
            array_push($this->deck[$value[1]], $value[0]);
            unset($this->pile[$key]);
        }
        $this->updateDBDeck();
    }

    /**
    * Mets à jour les informations des joueurs en base
    *
    * @param str $cardName Le nom de la carte à jouer
    *
    * @return boolean Si la carte a bien été posée
    */
    public function playCard($cardname)
    {
        $cardnameSplit = explode(',', $cardname)[0];
        $ret = false;
        if (!$this->isPlayable($cardname)) {
            return $ret;
        }
        array_push($this->pile, $cardnameSplit);
        foreach ($this->players as $key => $value) {
            if ($value->cookie == $_COOKIE['player']) {
                unset($value->cards[array_search($cardnameSplit, $value->cards)]);
                $this->players[$key]->cards = array_values($value->cards);
                return $this->updateTurnDB($cardname);
            }
        }
    }

    /**
    * Mets à jour la liste des joueurs encore en course
    *
    */
    private function getActivePlayers()
    {
        foreach ($this->players as $key => $value) {
            if (count($value->cards) > 0 && !in_array($value, $this->activePlayers)) {
                array_push($this->activePlayers, $value);
            }
        }
    }

    /**
    * Choisit une carte aléatoire dans la pile de deck
    *
    */
    private function randomStack($playerid)
    {
        if (count($this->deck['red']) == 0
        &&  count($this->deck['blue']) == 0
        &&  count($this->deck['yellow']) == 0
        &&  count($this->deck['green']) == 0
        &&  count($this->deck['joker']) == 0) {
            $this->reShuffle();
        }
        $playerid == 9 ? $stack = rand(0, 11) : $stack = rand(0, 12);
        switch ($stack) {
            case filter_var($rand, FILTER_VALIDATE_INT, ['options' => ['min_range' => '0', 'max_range' => '2']]):
                $stack = 'red';
                break;
            case filter_var($rand, FILTER_VALIDATE_INT, ['options' => ['min_range' => '3', 'max_range' => '5']]):
                $stack = 'blue';
                break;
            case filter_var($rand, FILTER_VALIDATE_INT, ['options' => ['min_range' => '6', 'max_range' => '8']]):
                $stack = 'yellow';
                break;  
            case filter_var($rand, FILTER_VALIDATE_INT, ['options' => ['min_range' => '9', 'max_range' => '11']]):
                $stack = 'green';
                break;
            case 12: // Le joker est le plus rare à piocher, car il y a moins de joker que d'autre cartes
                $stack = 'joker';
                break;
            default:
                break;
        }
        if (count($this->deck[$stack]) == 0) {
            return $this->randomStack($playerid);
        }
        return $stack;
    }


//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////
                    //                                                      //
                    //                  ** ACTIONS DB **                    //
                    //                                                      //
                    //////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

    /**
    * Mets la base de données à jour pour ce tour
    *
    * @param str $cardName Le nom de la carte à jouer (null si le joueur pioche)
    *
    */
    public function updateTurnDB($cardName)
    {

        // Si ce n'est pas à notre tour de jouer, on ne fais rien !
        if (!$this->yourTurn()) { return; }

        $effect     = (int) $this->isDrawCard($cardName);         // Effet à appliquer sur la partie
        $turn       = $this->isSkipCard($cardName);               // Facteur du calcul pour le prochain tour
        $turnover   = $this->isReveCard($cardName);               // Le turnover de la partie
        $index      = $this->getPlayerIndex();                    // L'index du joueur
        $uno        = (int) $this->Uno($this->players[$index]);   // Vérification si le joueur n'a plus qu'une carte en main
        $this->getActivePlayers();                                // Recupere les joueurs actifs de la partie
        
        $nextplayer = $this->getNextTurnPlayer($index, $turn, $turnover);

        if ((count($this->activePlayers) == 2 && ($turnover == 0 || $turn == 2)) || $uno == 1) {
            $nextTurn       = $this->players[$index]->cookie;
            $nextUsername   = $this->players[$index]->username;
        } else {
            $nextTurn       = $nextplayer->cookie;
            $nextUsername   = $nextplayer->username;
        } // Définit le prochain joueur

        if ($cardName == null) {
            $cardName = $this->roomInfos['lastcard'];
        } // Si le joueur pioche et ne joue pas, la lastcard ne change pas

        // ATTRIBUTION DU MESSAGE
        if ($effect == 1) {
            $msg = "Ouille ... ça va faire mal pour {$nextplayer->username} !!";
        } else if ($turnover == 0) {
            $msg = "Allez hop, on retourne tout !";
        } else if ($uno == 1) {
            $msg = "Hop, le mot magique ?! :-)";
        } else if ($this->isJoker($this->roomInfos['lastcard']) || $this->isJoker($cardName)) {
            if ($this->isJoker($cardName)) {
                $color = strtoupper(explode(',', $cardName)[1]);
            } else {
                $color = strtoupper(explode(',', $this->roomInfos['lastcard'])[1]);
            }
            $msg = 'On annonce ça ... en ' . $color . '!';
        } else {
            $msg = "Au tour de {$nextUsername} !";
        } // :-)
        //////

        $nb = $this->roomInfos['nb'] + 1;               // Passage au tour suivant
        $decodePlayers = json_encode($this->players);
        $playersDB = '{
            "players": '
                    . $decodePlayers .
            '}';
        $pileDB = ( json_encode( $this->pile ) );
        $deckDB = ( json_encode( $this->deck ) );

        $this->updateCol(   'uno_room',
                            ['players', 'lastcard', 'deck', 'pile', 'turn', 'turnover', 'nb', 'msg', 'uno', 'effect'],
                            "name = '{$this->roomID}'",
                            [ $playersDB, $cardName, $deckDB, $pileDB, $nextTurn, $this->roomInfos['turnover'], $nb, $msg, $uno, $effect ], false);
        return true;
    }
    
    /**
    * Mets à jour les informations de la lastcard en base
    *
    */
    private function setLastcard($card)
    {
        if ($card == null) { return; }
        $this->lastcard = $card;
        $this->updateCol('uno_room', ['lastcard'], "name = '{$this->roomID}'", [$card], false);
    }

    /**
    * Mets à jour les informations du deck en DB
    *
    */
    public function updateDBDeck()
    {
        $deckDB = ( json_encode( $this->deck ) );
        $this->updateCol('uno_room', ['deck'], "name = '{$this->roomID}'", [$deckDB], false);
    }

    /**
    * Mets à jour les informations de la pile, du deck et des joueurs en DB
    *
    */
    public function updateDrawCard()
    {
        $pileDB         = ( json_encode( $this->pile ) );
        $decodePlayers  = json_encode($this->players);
        $deckDB         = ( json_encode( $this->deck ) );
        $playersDB = '{
            "players": '
                    . $decodePlayers .
            '}';
        $this->updateCol('uno_room', ['pile', 'players', 'deck'], "name = '{$this->roomID}'", [$pileDB, $playersDB, $deckDB], false);
    }

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////
                    //                                                      //
                    //                  ** CHECK FCTS **                    //
                    //                                                      //
                    //////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

    /**
    * Retourne true ou false en fonction de si la carte est une carte pioche
    *
    * @param str $cardname Nom de la carte
    *
    * @return boolean
    */
    private function isDrawCard($cardname)
    {
        $cardnameSplit = explode(',', $cardname)[0];
        return in_array(substr($cardname, 0, 2), ['+2', '+4']);
    }
    

    /**
    * Retourne true ou false en fonction de si la carte est une carte Skip
    *
    * @param str $cardname Nom de la carte
    *
    * @return boolean
    */
    private function isSkipCard($cardname)
    {
        $cardnameSplit = explode(',', $cardname)[0];
        if (substr($cardname, 0, 1) == 'p') {
            return 2;
        }
        return 1;
    }
    
    /**
    * Retourne true ou false en fonction de si la carte est une carte Reverse
    *
    * @param str $cardname Nom de la carte
    *
    * @return boolean
    */
    private function isReveCard($cardname)
    {
        $cardnameSplit = explode(',', $cardname)[0];
        return substr($cardname, 0, 1) != 'r';
    }

    /**
    * Vérifie si la carte saisie en paramètre est à effet
    *
    * @param str $cardName Le nom de la carte
    *
    * @return boolean Si la carte est bien une carte à effet
    */
    private function isSpecialCard($cardName)
    {
        return      $this->isReveCard($card)
                ||  $this->isSkipCard($card) == 1
                ||  $this->isDrawCard($card);
    }

    
    /**
    * Vérifie si c'est le tour du joueur
    *
    * @return boolean
    */
    public function yourTurn()
    {
        return $_COOKIE['player'] == $this->roomInfos['turn'];
    }

    /**
    * Vérifie si une carte est un joker
    *
    * @param str Le nom de la carte
    *
    * @return boolean
    */
    private function isJoker($cardName)
    {
        $cardName = explode('_', $cardName)[1];
        return substr($cardName, 0, 5) == 'joker';
    }

    /**
    * Vérifie si le joueur est bien en UNO
    *
    * @param obj $player Objet du joueur
    *
    * @return boolean Si le joueur est bien en UNO
    */
    public function Uno($player)
    {
        return !count($player->cards) > 1;
    }

    /**
    * Vérifie si la carte saisie en paramètre est jouable
    *
    * @param str $cardName Le nom de la carte à jouer
    *
    * @return boolean Si la carte est bien joauble
    */
    public function isPlayable($cardName)
    {
        $splitCard = explode('_', $cardName);
        $lastcard = $this->roomInfos['lastcard'];
        if ($this->isJoker($cardName)) {
            return true;
        } else if ($this->isJoker($lastcard)) {
            $lastcard = explode(',', $lastcard)[1];
        }
        $lastcard = explode('_', $lastcard);
        
        foreach($splitCard as $item) {
            if ($item == $lastcard[0]) { return true; }
            else if (isset($lastcard[1]) && $item == $lastcard[1]) { return true; }
        }
        return false;
    }

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
                    //////////////////////////////////////////////////////////
                    //                                                      //
                    //                  ** GETTER **                        //
                    //                                                      //
                    //////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////


    /**
    * Retourne l'index du joueur
    *
    * @return number
    */
    public function getPlayerIndex()
    {
        foreach ($this->players as $key => $value) {
            if ($value->cookie == $this->roomInfos['turn']) {
                return $key;
            }
        }
    }

    /**
    * Retourne l'index du joueur en activePlayer
    *
    * @return number
    */
    public function getActivePlayerIndex()
    {
        foreach ($this->activePlayers as $key => $value) {
            if ($value->cookie == $this->roomInfos['turn']) {
                return $key;
            }
        }
    }

    /**
    * Retourne les joueurs encore actifs dans la partie
    *
    * @return array
    */
    private function activePlayers()
    {
        $active = [];
        foreach ($this->players as $key => $value) {
            if (count($value->cards) > 0) {
                $active = $value;
            }
        }
        return $active;
    }

    /**
    * Récupère le prochain joueur
    *
    * @param number $index index du joueur actuel
    * @param number $turn Facteur de calcul pour le prochain joueur (prochain, ou sur-prochain)
    * @param number $turnover Tour horaire ou antihoraire de la partie
    *
    * @return object Retourne le prochain joueur
    */
    private function getNextTurnPlayer($index, $turn, $turnover)
    {
        if ($turnover == 0) {
            $turnover = ($this->roomInfos['turnover'] == 0 ? 1 : 0);
            $this->roomInfos['turnover'] = $turnover;
        }
        while (current($this->activePlayers)->cookie != $_COOKIE['player']) {
            next($this->activePlayers);
        }
        for ($i=0; $i < $turn; $i++) { 
            if ($turnover == 1) {
                next($this->activePlayers) ? '' : reset($this->activePlayers);
            } else {
                prev($this->activePlayers) ? '' : end($this->activePlayers);
            }
        }
        return current($this->activePlayers);
    }

}