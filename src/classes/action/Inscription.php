<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\CompteException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;

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
        $r = NRVRepository::getInstance();

        $username = $_POST["username"] ?? '';
        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';

        $s = '<div class="container">';
        try {
            $user = new User($username,$email);
            $resInscr = $r->inscription($user->__get("username"), $user->__get("email"), $password, User::$STANDARD);
            $s .= $resInscr;
        } catch (CompteException $e) {
            $erreur = $e->getMessage();
            echo "<script>window.alert($erreur);</script>";
            $s = $this->executeGet();


        }
        return $s;
    }
}