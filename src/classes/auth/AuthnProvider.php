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
        try {
            $user = $r->getUserFromMail($username); // test si l'utilisateur se connecte avec son mail
        }catch (AuthnException $e){
            $user = $r->getUserFromUsername($username); // test si l'utilisateur se connecte avec son username
        }
        $userPass = $r->getPasswordFromUser($user);

        if (!password_verify($password, $userPass)) {
            throw new AuthnException("Mot de passe incorrect");
        }

        $_SESSION['user'] = serialize($user);
    }

    public static function register(string $username, string $email, string $password)  // enregistre un nouvel utilisateur
    {
        $r = NRVRepository::getInstance();
        try {
            $user = $r->getUserFromMail($email);
            $user = $r->getUserFromUsername($username);
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

        $user = new User($username, $email);
        $user = $r->inscription($user, $password, User::$STANDARD);

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