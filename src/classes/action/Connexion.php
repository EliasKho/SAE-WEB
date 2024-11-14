<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\exception\CompteException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;

/**
 * Classe Connexion qui permet de se connecter
 */
class Connexion extends Action {

    /**
     * Methode qui s'execute quand la page est chargée, affiche le formulaire de connexion
     */
    public function executeGet(): string {
        $form = '<div class="container">
                    <h2>Connexion</h2>
                    <form action="?action=connexion" method="post">
                        <input type="text" name="Username" placeholder="Nom d\'utilisateur/Email" required>
                        <input type="password" name="Password" placeholder="Mot de passe" required>
                        <button type="submit">Valider</button>
                    </form>
                    <p>' . ($message ?? '') . '</p>
                 </div>';
        return $form;
    }

    /**
     * Methode qui s'execute quand le bouton est cliqué
     * @return string
     */
    protected function executePost(): string
    {
        // Récupération des données du formulaire
        $username = $_POST['Username'];
        $password = $_POST['Password'];
        // On essaye de se connecter
        try {
            AuthnProvider::signin($username, $password);
        } catch (AuthnException $e) {
            return $e->getMessage();
        }
        // On récupère l'utilisateur connecté
        $user = unserialize($_SESSION['user']);
        // On retourne un message de bienvenue
        return "Vous êtes connecté, bienvenue " . $user->username;
    }
}