<?php

namespace iutnc\nrv\action;

use iutnc\deefy\bd\ConnectionFactory;
use iutnc\deefy\compte\compteUtil;
use iutnc\deefy\exception\CompteException;

class Connexion extends Action {

    /**
     * Methode qui permet a un utilisateur de se connecter
     * @param $username
     * @param $password
     * @return void
     * @throws CompteException
     */
    public static function connexion($username, $password) {
        $bd = ConnectionFactory::makeConnection();
        $st = $bd->prepare("SELECT * FROM users WHERE username = :username");
        $st->execute(['username' => $username]);

        $user = $st->fetch();

        if ($user) {
            $dbPassword = $user['password'];

            // Vérifie si le mot de passe est hashé en utilisant une regex simple
            $isHashed = preg_match('/^\$2y\$/', $dbPassword);

            if (($isHashed && password_verify($password, $dbPassword)) || (!$isHashed && $password === $dbPassword)) {
                // Si le mot de passe est correct, créer l'utilisateur en session
                $_SESSION['connection'] = new compteUtil($user['username'], $user['email'], $user['role']);
            } else {
                // Mot de passe incorrect
                throw new CompteException("La connexion a échoué. Vérifiez votre nom d'utilisateur et votre mot de passe.");
            }
        } else {
            // Aucun utilisateur trouvé
            throw new CompteException("La connexion a échoué. Vérifiez votre nom d'utilisateur et votre mot de passe.");
        }
    }

    protected function executeGet(): string
    {
        // TODO: Implement executeGet() method.
    }

    protected function executePost(): string
    {
        // TODO: Implement executePost() method.
    }
}