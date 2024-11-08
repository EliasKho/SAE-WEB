<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;
class AuthnProvider
{

    public static function signin(string $username, string $password): void
    {
        $r = NRVRepository::getInstance();
        $user = $r->getUserFromUsername($username); // recupere le mdp hashé dans la bd
        $userPass = $r->getPasswordFromUser($user);

        if (!password_verify($password, $userPass)) {
            throw new AuthnException("Mot de passe incorrect");
        }

        $_SESSION['user'] = serialize($user);
    }

    public static function register(string $email, string $password)  // enregistre un nouvel utilisateur
    {
        $r = NRVRepository::getInstance();
        try {
            $user = $r->getUserFromMail($email);
        } catch (AuthnException $e) {
            $user = null;
        }

        if (strlen($password) < 10) { // password trop court
            throw new AuthnException("Mot de passe trop court (10 caractères minimum)");
        }

        if ($user != null) { // deja un utilisateur avec cet email
            throw new AuthnException("Email déjà utilisé");
        }

        $password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);

//        $user = new User($email, $password);
        $r->insertUser($user);

        $_SESSION['user'] = serialize($user);
    }

    public static function getSignedInUser(): User
    {
        if (!isset($_SESSION['user'])) {
            throw AuthnException::notSignedIn();
        }
        return unserialize($_SESSION['user']);
    }

    public static function signout(): void
    {
        AuthnProvider::getSignedInUser();
        unset($_SESSION['user']);
    }
}