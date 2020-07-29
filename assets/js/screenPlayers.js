const url    = 'http://localhost/';
const roomID = getQueryVariable('id');

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

    /* Expulse un joueur de la room */
    function expulse(query)
    {
        let queryURL = url + 'action.php?expulse=' + query;
        let id = query.substr(0, 1);
        $.ajax({
                url         :   queryURL,
                dataType    :   "JSON",
                type        :   "GET",
                success     :   function( response ) {
                    if (response == true) {
                        let el = document.getElementById(id);
                        let nb = parseInt(document.getElementById('online_players').innerHTML);
                        el.remove();
                        nb = nb - 1;
                        document.getElementById('online_players').innerHTML = nb;
                        if (nb == 1) {
                            createWaitingPlayers();
                        }
                    }
                }
        });
    }

    /* Affiche le message d'attente */
    function createWaitingPlayers()
    {
        let strong = document.createElement('STRONG');
        let span = document.createElement('SPAN');
        let progress = document.getElementById('progress');

        progress.innerHTML = '';

        strong.innerHTML = "En attente d'autre joueurs ...&nbsp;&nbsp;";

        span.setAttribute('class', 'spinner-grow spinner-grow-sm text-light');
        span.setAttribute('role', 'status');

        progress.appendChild(strong);
        progress.appendChild(span);
    }

    /* Dessine le bouton pour démarrer la partie dans le DOM */
    function createStartBtn()
    {
        let a = document.createElement('A');
        let strong = document.createElement('STRONG');
        let progress = document.getElementById('progress');

        progress.innerHTML = '';
        strong.innerHTML = "Lancer la partie";

        a.setAttribute('class', 'btn btn-warning btn-block btn-sm shadow');
        a.setAttribute('role', 'button');
        a.setAttribute('data-bs-hover-animate', 'pulse');
        a.setAttribute('style', 'background: url(assets/img/uno_bg.png); background-size: cover');
        a.setAttribute('onclick', "startGame()");

        a.appendChild(strong);
        progress.appendChild(a);
    }


    /* Ajoute une ligne au tableau des joueurs */
    function addLineTab(referenceNode, username, id, admin)
    {
        let tr = document.createElement('TR');
        let td_1 = document.createElement('TD');
        let td_2 = document.createElement('TD');
        let nb = parseInt(document.getElementById('online_players').innerHTML);

        tr.setAttribute('name', 'user');
        tr.setAttribute('id', id);

        td_1.innerHTML = username;

        tr.appendChild(td_1);
        if (admin === true) {
            let i = document.createElement('I');
            i.setAttribute('class', 'fa fa-close');
            i.setAttribute('onclick', 'expulse("' + id + '_' + roomID + '")')
            td_2.appendChild(i);
            tr.appendChild(td_2);
            if (document.getElementsByName('user').length < 3) {
                createStartBtn();
            }
        }
        referenceNode.parentNode.insertBefore(tr, referenceNode.nextSibling);

        nb = nb + 1;
        document.getElementById('online_players').innerHTML = nb;
    }

    /* Vérifie si le visiteur est bien inscrit dans la room */
    function checkAvailable()
    {
        let queryURL = url + 'action.php?inRoom=' + roomID;
        var res;
        $.ajax({
                url         :   queryURL,
                dataType    :   "JSON",
                type        :   "GET",
                async       :   false,
                success     :   function (data) {
                    res = data; 
                }
        });
        return res;
    }

    function startGame()
    {
        let queryURL = url + 'action.php?startRoom=' + roomID;
        $.ajax({
                url         :   queryURL,
                dataType    :   "JSON",
                type        :   "GET",
                success     :   function (data) {
                    console.log(data.data)
                }
        });
    }

    setInterval(function()
    {
        let queryURL = url + 'action.php?getInfoRoom=' + roomID;
        var self = this;
        $.ajax({
            url         :   queryURL,
            dataType    :   "JSON",
            type        :   "GET",
            success     :   function( response ) {
                let nbWritten = document.getElementsByName('user').length;
                if (checkAvailable() == false) {
                    window.location.href = url;
                    alert('Vous avez été exclu de la partie :-(');
                } // Si le joueur a été exclu, il est redirigé vers la page d'accueil
                for (let index = nbWritten; index < response.players.length; index++) {
                    addLineTab(document.getElementById(index - 1), response.players[index].username, index, response.admin);
                }
                if (response.status == 2) {
                    document.location.reload();
                }
            }
        });
    }, 2000);