<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\exception\CompteException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;
use iutnc\nrv\auth\AuthnProvider;

/**
 * Class Inscription : Action d'inscription
 */
class Inscription extends Action {

    /**
     * Formulaire d'inscription
     */
    protected function executeGet(): string
    {
        $s = '<div class="container">';
        $s .= "<h2>Inscription</h2>";

        // Formulaire HTML
        $s .= '<form id="f1" action="?action=inscription" method="post">
                <input type="text" name="username" placeholder="Nom d\'utilisateur" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Valider</button>
               </form>';
        $s .= '</div>';
        return $s;
    }

    /**
     * Traitement de l'inscription
     */
    protected function executePost(): string
    {
        // Récupération des données du formulaire
        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        // on essaye de créer le compte
        try {
            AuthnProvider::register($username, $email, $password,1);
        } catch (AuthnException $e) {
            // en cas d'erreur, on affiche un message d'erreur
            $message = $e->getMessage();
            echo "<script>window.onload = ()=>{window.alert('$message');};</script>";
            return "Erreur lors de l'inscription : ".$e->getMessage();
        }
        // on affiche un message de succès
        return "Inscription réussie";
    }
}