const   url     = 'http://localhost/';
/* const url = 'https://dilaouid.xyz/uno/'; */
const   roomID  = getQueryVariable('id');
var     nb      = -1;

var hand = document.getElementsByClassName('card-hand');

/* Parse les query string */
function getQueryVariable(variable)
{
    let query = window.location.search.substring(1);
    let vars = query.split('&');
    for (let i = 0; i < vars.length; i++) {
        let pair = vars[i].split('=');
        if (decodeURIComponent(pair[0]) == variable) {
            return decodeURIComponent(pair[1]);
        }
    }
    console.log('Query %s inexistante', variable);
}

function fillYourHand(hand, your_turn, effect)
{
    var rowPlayer   = document.getElementById('rowPlayer');
    rowPlayer.innerHTML = '';
    hand.forEach(el => {
        let card                = el.split('_');
        var imgCardHand         = document.createElement('IMG');
        var divCardHand         = document.createElement('DIV');

        imgCardHand.className = 'shadow-lg card-hand';
        imgCardHand.src       = `assets/img/cards/${card[1]}/${card[0]}.png`;
        imgCardHand.id        = el;
        if (your_turn === true && effect == false) {
            imgCardHand.setAttribute('onclick', `play('${el}')`)
        }
        divCardHand.appendChild(imgCardHand);

        divCardHand.className = 'col-auto align-self-center';
        divCardHand.id        = 'cardHand';

        rowPlayer.appendChild(divCardHand);

    });
}

function checkJoker(cardname) {
    return cardname.split('_')[1] == 'joker';
}


/**
 * Placer une carte
 * @function
 * @param {string} cardname - Nom de la carte à jouer
 */
function playcard(cardname)
{
    let queryURL = url + 'action.php?playcard=' + roomID + '%' + cardname;
    $.ajax({
        url         :   queryURL,
        dataType    :   "JSON",
        type        :   "GET",
        success     :   function( res ) {
            if (res === false) {
                console.log('Il n\'est pas possible de jouer cette carte pour le moment!... Tricheur? :-)');
            } else {
                playSound(playi)
                document.getElementById(cardname).remove();
                Array.from(hand).forEach(el => {
                    el.removeAttribute('onclick');
                });
                let btn = document.getElementsByName('pickcolor')
                Array.from(btn).forEach(el => {
                    el.classList.add('d-none');
                    el.removeAttribute('onclick');
                });
                let pioche = document.getElementById('pioche');
                pioche.classList.add('d-none');
            }
        }
    }).fail(function (res) {
        playSound(error)
    });
}

/**
 * Jouer une carte joker.
 * @function
 * @param {string} effect - Effet de la carte joker
 * @param {string} color - Couleur choisie
 */
function pickColor(effect, color)
{
    if (['+4', '+2', 'n'].includes(effect)) {
        let cardPlaying = effect + '_joker,' + color;
        Array.from(hand).forEach(el => {
            el.removeAttribute('onclick');
        });
        let btn = document.getElementsByName('pickcolor')
        Array.from(btn).forEach(el => {
            el.classList.add('d-none');
            el.removeAttribute('onclick');
        });
        let pioche = document.getElementById('pioche');
        pioche.classList.add('d-none');
        playcard(cardPlaying);
    }
}

/**
 * Affiche les boutons pour choisir la prochaine couleur lors de l'utilisation d'un joker
 * @function
 * @param {string} effect - Effet de la carte joker
 */
function showColorChoice(effect)
{
    hideEl(['pioche']);
    let choiceColors = {
        0: 'red',
        1: 'green',
        2: 'yellow',
        3: 'blue'
    };
    let i = 0;
    let btn = document.getElementsByName('pickcolor')
    Array.from(btn).forEach(el => {
        el.classList.remove('d-none');
        el.setAttribute('onclick', `pickColor('${effect}', '${choiceColors[i++]}')`);
    });
    Array.from(hand).forEach(ele => {
        ele.removeAttribute('onclick');
    });
}

/**
 * Vérification pour jouer la carte.
 * @function
 * @param {string} cardname - Nom de la carte en main.
 */
function play(cardname)
{
    
    // Format d'une carte               ==> TYPE_COULEUR
    // Format d'une carte posée joker   ==> N_EFFET, NEWCOLOR

    let queryURL = url + 'action.php?getInfoGame=' + roomID;
    let playedcard = cardname.split('_');
    let joker = checkJoker(cardname);
    $.ajax({
        url         :   queryURL,
        dataType    :   "JSON",
        type        :   "GET",
        success     :   function( res ) {
            if (res.your_turn == false) {
                console.log('Attends ton tour !')
                return ;
            } else if (!res.hand.includes(cardname) || res.effect == true) {
                console.log('Tricheur');
                return ;
            }
            if (checkJoker(res.lastcard) && res.lastcard.split(',')[1] != playedcard[1] && joker == false) {
                // Si la carte jouée n'est ni un joker, ni la couleur annoncée
                return ;
            }
            joker == true ? showColorChoice(playedcard[0]) : playcard(cardname);
            return;
        },
    });
}

function draw()
{
    let queryURL = url + 'action.php?draw=' + roomID;
    $.ajax({
        url         :   queryURL,
        dataType    :   "JSON",
        type        :   "GET",
        success     :   function( res ) {
            if (res == false) {
                playSound(error)
                console.log('Impossible de piocher pour l\'instant, déso !');
                return ;
            }
            Array.from(hand).forEach(el => {
                el.removeAttribute('onclick');
            });
            let btn = document.getElementsByName('pickcolor')
            Array.from(btn).forEach(el => {
                el.classList.add('d-none');
                el.removeAttribute('onclick');
            });
            let pioche = document.getElementById('pioche');
            pioche.classList.add('d-none');
            playSound(drawc)
        }
    });
}

function getLastCard(lastcard)
{
    let last = document.getElementById('lastcard');
    last.src = `assets/img/cards/${lastcard[1]}/${lastcard[0]}.png`;
}

function hideOngoing()
{
    let ongoing = document.getElementsByClassName('ongoing');
    Array.from(ongoing).forEach(el => {
        el.classList.add('d-none');
    });
}

function fillPlayersHand(opp)
{
    Object.entries(opp).forEach(([key, value]) => {
        let hand = document.getElementById('hand_player_' + key);
        if (value > 0) {
            hand.innerHTML = '';
            for (let i = 0; i < value; i++) {
                let imgCardHand         = document.createElement('IMG');
                let divCardHand         = document.createElement('DIV');

                divCardHand.className = 'col';

                imgCardHand.className = 'handplayer';
                imgCardHand.src       = 'assets/img/cards/back.png';

                divCardHand.appendChild(imgCardHand);
                hand.appendChild(divCardHand);
            }
        } else {
            // Si le joueur n'a plus de cartes en main,
            // mettre une image comme quoi il a gagné !
        }
    });
}

function showEl(arr)
{
    arr.forEach(el => {
        document.getElementById(el).classList.remove('d-none');
    });
}

function hideEl(arr)
{
    arr.forEach(el => {
        document.getElementById(el).classList.add('d-none');
    });
}

function uno()
{
    let queryURL = url + 'action.php?uno=' + roomID;
    var self = this;
    $.ajax({
        url         :   queryURL,
        dataType    :   "JSON",
        type        :   "GET",
        success     :   function( res ) {
            if (!res) { console.log('Tricheur'); }
            hideEl(['contreUno', 'Uno']);
        }
    });
}

setInterval(function()
{
    let queryURL = url + 'action.php?getInfoGame=' + roomID;
    var self = this;
    $.ajax({
        url         :   queryURL,
        dataType    :   "JSON",
        type        :   "GET",
        success     :   function( res ) {
            if (nb === res.nb) { return; } // Si on n'a pas changé de tour, pas besoin de manipuler le DOM
            if (res.uno == true) {
                hideEl(['pioche']);
                res.your_turn ? showEl(['Uno']) : showEl(['contreUno']);
                return ;
            }
            hideEl(['contreUno', 'Uno']);
            let printableLastCard = res.lastcard.split(',')[0];
            nb = res.nb;
            hideOngoing(); // On cache les spinners
            if (res.your_turn === true) {
                let cursedCars = ['+2', '+4'];
                let printableLast_split = printableLastCard.split('_')[0]
                if (cursedCars.includes(printableLast_split) && res.effect == 1) {
                    document.getElementById('nbDraw').innerHTML = `(x${printableLast_split.substr(1, 2)})`;
                } else {
                    document.getElementById('nbDraw').innerHTML = '';
                }
                showEl(['pioche']);
            } else {
                hideEl(['pioche']);
                showEl(['ongoing_' + res.turn]);
                Array.from(hand).forEach(ele => {
                    ele.removeAttribute('onclick');
                });
            }
            fillYourHand(res.hand, res.your_turn, res.effect);
            fillPlayersHand(res.opponents);
            getLastCard(printableLastCard.split('_')); // Affiche la lastcard
            document.getElementById('msgInfo').innerHTML = res.msg; // Affiche le message de gauche
        }
    });
}, 1000);