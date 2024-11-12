<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\exception\CompteException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;
use iutnc\nrv\auth\AuthnProvider;

class Inscription extends Action {

    public function checkPasswordStrength(string $pass, int $minimumLength = 8): bool
    {
        $length = strlen($pass) >= $minimumLength;
        $digit = preg_match("#[0-9]#", $pass);
        $special = preg_match("#[\W]#", $pass);
        $lower = preg_match("#[a-z]#", $pass);
        $upper = preg_match("#[A-Z]#", $pass);
        return $length && $digit && $special && $lower && $upper;
    }


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

    protected function executePost(): string
    {
        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = $_POST["password"];

        try {
            AuthnProvider::register($username, $email, $password);
        } catch (AuthnException $e) {
            $message = $e->getMessage();
            echo "<script>window.onload = ()=>{window.alert('$message');};</script>";
            return "Erreur lors de l'inscription : ".$e->getMessage();
        }
        return "Inscription réussie";
    }
}