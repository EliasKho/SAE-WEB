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

/**
 * Action pour créer un staff
 */
class CreerStaff extends Action
{
    /**
     * formulaire pour ajouter un staff
     */
    protected function executeGet(): string
    {
        // Vérification des droits, seul un admin peut ajouter un staff
        $user = AuthnProvider::getSignedInUser();
        $authz = new Authz($user);
        try {
            $authz->checkRole(User::$ADMIN);
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

    /**
     * Création d'un staff
     */
    protected function executePost(): string
    {
        // Vérification des droits, seul un admin peut ajouter un staff
        $user = AuthnProvider::getSignedInUser();
        $authz = new Authz($user);
        try {
            $authz->checkRole(User::$ADMIN);
        } catch (AuthorizationException $e) {
            return $e->getMessage();
        }
        // Récupération des données du formulaire
        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];

        // Création du staff
        try {
            // Création du compte avec le rôle staff
            AuthnProvider::register($username,$email,$password,User::$STAFF);
        } catch (Exception $e) {
            return "Erreur lors de l'authentification : ".$e->getMessage();
        }
        // Retourne un message de succès
        return "Inscription réussie";
    }
}