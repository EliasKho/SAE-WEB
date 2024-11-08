<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\exception\CompteException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;


class Connexion extends Action {

    /**
     * Methode qui s'execute quand le bouton est cliqué
     * @return string
     */
    public function executeGet(): string {
        $form = '<div class="container">
                    <h2>Connexion</h2>
                    <form action="?action=connexion" method="post">
                        <input type="text" name="Username" placeholder="Nom d\'utilisateur" required>
                        <input type="password" name="Password" placeholder="Mot de passe" required>
                        <button type="submit">Valider</button>
                    </form>
                    <p>' . ($message ?? '') . '</p>
                 </div>';
        return $form;
    }

    protected function executePost(): string
    {
        $username = $_POST['Username'];
        $password = $_POST['Password'];
        try {
            AuthnProvider::signin($username, $password);
        } catch (AuthnException $e) {
            return $e->getMessage();
        }
        $user = unserialize($_SESSION['user']);
        return "Vous êtes connecté, bienvenue " . $user->username;
    }
}