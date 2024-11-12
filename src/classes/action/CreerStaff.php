<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\exception\AuthorizationException;
use iutnc\nrv\exception\CompteException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;
use Exception;

class CreerStaff extends Action
{
    /**
     * @throws AuthnException
     * @throws AuthorizationException
     */
    protected function executeGet(): string
    {
        $user = AuthnProvider::getSignedInUser();
        $authz = new Authz($user);
        try {
            $authz->checkRole(3);
        } catch (AuthorizationException $e) {
            return $e->getMessage();
        }
        $s = '<div class="container">';
        $s .= "<h2>Ajout staff</h2>";

        // Formulaire HTML
        $s .= '<form id="f2" action="?action=creerStaff" method="post">
                <input type="text" name="username" placeholder="Nom d\'utilisateur" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Valider</button>
               </form>';
        $s .= '</div>';
        return $s;
    }

    protected function executePost(): string
    {
        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];

        try {
            AuthnProvider::register($username,$email,$password,2);
        } catch (Exception $e) {
            return "Erreur lors de l'authentification : ".$e->getMessage();
        }
        return "Inscription réussie";
    }
}