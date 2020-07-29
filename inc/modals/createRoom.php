<div class="modal fade shadow-lg" role="dialog" tabindex="-1" id="newRoom">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header text-white-50" style="background: url(assets/img/modalHeader.png) center / cover repeat-y;">
                <h3 class="modal-title">Créer un salon</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="text-black-50" for="players">Nombre de joueurs autorisés</label>
                        <select class="custom-select custom-select-lg" name="players" required>
                            <option value="2" selected="">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="text-black-50" for="players">Votre nom d'utilisateur</label>
                        <input class="form-control form-control-lg" type="text" name="username" required minlength="3" maxlength="12" placeholder="Nom d'utilisateur (ex: Killian, Skarmz92, etc...)">
                    </div>

                    <div class="modal-footer">
                    <?= $alert['createRoom']; ?>
                        <!-- <div class="alert alert-danger" role="alert">
                            <span><strong>Ce salon est complet !</strong></span>
                        </div> -->
                        <button class="btn btn-light" type="button" data-dismiss="modal">Fermer</button>
                        <button class="btn btn-primary text-monospace" type="submit" style="background: url(assets/img/buttonNew.png) center / cover;box-shadow: -1px 0px 20px 0px rgb(188,0,0);border-style: none;" name="create">Créer la partie</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>