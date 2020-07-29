var enableSound = false;

var bgm     = new Audio('assets/audio/new-game-ost-03-mainichi-egao-de-ganbarimasu.mp3');
var error   = new Audio('assets/audio/error.m4a');
var playi   = new Audio('assets/audio/play.mp3');
var drawc   = new Audio('assets/audio/draw.m4a');
var icon    = document.getElementById('volume');

function volumeOn()
{
    enableSound = true;
    icon.classList.replace('fa-volume-off', 'fa-volume-up');
    icon.setAttribute('onclick', 'volumeOff()');
    bgm.loop = true;
    bgm.volume = 0.5;
    playSound(bgm);
}

function volumeOff()
{
    enableSound = false;
    icon.classList.replace('fa-volume-up', 'fa-volume-off');
    icon.setAttribute('onclick', 'volumeOn()');
    bgm.pause();
}

function playSound(sound)
{
    if (!enableSound) { return; }
    sound.play();
}