<div class="modal fade shadow-lg" role="dialog" tabindex="-1" id="joinRoom">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-white-50" style="background: url(assets/img/buttonJoin.png) center / cover no-repeat;">
                <h3 class="modal-title">Rejoindre un salon</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="text-black-50" for="id">ID du salon</label>
                        <input class="form-control form-control-lg" type="text" name="id" required minlength="13" maxlength="13" placeholder="ex: 5eb28b9856307">
                    </div>
                    <div class="form-group">
                        <label class="text-black-50" for="players">Votre nom d'utilisateur</label>
                        <input class="form-control form-control-lg" type="text" name="username" required minlength="3" maxlength="12" placeholder="Nom d'utilisateur (ex: Killian, Skarmz92, etc...)">
                    </div>
                    <div class="modal-footer">
                        <?= $alert['joinRoom']; ?>
                        <!-- <div class="alert alert-danger" role="alert">
                            <span><strong>Ce salon est complet !</strong></span>
                        </div> -->
                        <button class="btn btn-light" type="button" data-dismiss="modal">Fermer</button>
                        <button class="btn btn-primary text-monospace" type="submit" style="background: url(assets/img/buttonJoin.png) center / cover;box-shadow: -1px 0px 20px 0px rgb(4,0,188);border-style: none;" name="join">Rejoindre la partie</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>