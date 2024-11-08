<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\exception\CompteException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;


class Connexion extends Action {

    /**
     * Methode qui permet a un utilisateur de se connecter
     * @param $username
     * @param $password
     * @return void
     * @throws CompteException
     */
    public static function connexion($username, $password) {
        $bd = NRVRepository::getInstance();
        $user = $bd->getUserFromMail();


        if ($user) {
            $dbPassword = $user['password'];

            // Vérifie si le mot de passe est hashé en utilisant une regex simple
            $isHashed = preg_match('/^\$2y\$/', $dbPassword);

            if (($isHashed && password_verify($password, $dbPassword)) || (!$isHashed && $password === $dbPassword)) {
                // Si le mot de passe est correct, créer l'utilisateur en session
                $_SESSION['connection'] = new User($user['username'], $user['email']); //role par defaut : standard
            } else {
                // Mot de passe incorrect
                throw new CompteException("La connexion a échoué. Vérifiez votre nom d'utilisateur et votre mot de passe.");
            }
        } else {
            // Aucun utilisateur trouvé
            throw new CompteException("La connexion a échoué. Vérifiez votre nom d'utilisateur et votre mot de passe.");
        }
    }

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