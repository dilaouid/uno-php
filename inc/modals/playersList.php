<div class="modal" tabindex="-1" style="margin-top: -41px;opacity: 0.80;" id="players" data-backdrop="static"
   data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="table-responsive" style="background-color: #343a40;">
                    <table class="table table-dark">
                        <thead>
                            <tr>
                                <th>Nom d'utilisateur</th>
                                <?php if ($admin) { echo '<th>Action</th>'; } ?>
                            </tr>
                        </thead>
                        <tbody id="bodyTable">
                            <?php
                                foreach ($roomInfos['players'] as $key => $value) {
                                    echo "<tr id='{$value->id}' name='user'><td>{$value->username}</td>";
                                    if ($admin && $value->cookie != $_COOKIE['player']) {
                                        echo "<td><i class=\"fa fa-close\" onclick=\"expulse('{$value->id}_{$roomInfos['name']}')\"></i></td>";
                                    }
                                    echo '</tr>';
                                }
                            ?>
                            <tr style="background: url(assets/img/modalHeader.png);background-size: cover;">
                            
                            <?php 
                            if ( count($roomInfos['players']) == 1 ) { 
                                include('inc/msg/waitingForPlayers.php');
                            } else if ( count($roomInfos['players']) > 1 && $admin ){
                                include('inc/btn/startGame.php');
                            } else {
                                include('inc/msg/waitingForAdmin.php');
                            }
                            /* 
                            include('inc/msg/waitingForPlayers.php');
                            include('inc/btn/startGame.php');
                            include('inc/msg/waitingForAdmin.php'); */

                            ?>
                            </div>
                            <td>(<span id="online_players"><?= count($roomInfos['players']); ?></span> / <?= $roomInfos['nb_players']; ?>)</td>
                            </tr>
                            <tr class="text-center">
                                <th class="text-center" colspan="2">ID du salon:&nbsp;<?= $roomID ?><br><em>(ce salon sera supprimé 30 minutes après sa création)</em></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>