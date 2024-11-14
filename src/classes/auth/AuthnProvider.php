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
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $user = $r->getUserFromMail($username);
        } else {
            $user = $r->getUserFromUsername($username);
        }
        $userPass = $r->getPasswordFromId($user->id);

        if (!password_verify($password, $userPass)) {
            throw new AuthnException("Mot de passe incorrect");
        }

        self::setPreferences($user);

        $_SESSION['user'] = serialize($user);
    }

    public static function register(string $username, string $email, string $password,int $role)  // enregistre un nouvel utilisateur
    {
        $r = NRVRepository::getInstance();
        try {
            $r->getUserFromMail($email);
            throw new AuthnException("E");
        } catch (AuthnException $e) {
            if ($e->getMessage() == "E") {
                throw new AuthnException("Un utilisateur avec cet email existe déjà");
            }
            try{
                $r->getUserFromUsername($username);
                throw new AuthnException("U");
            } catch (AuthnException $e) {
                if ($e->getMessage() == "U") {
                    throw new AuthnException("Un utilisateur avec ce nom d'utilisateur existe déjà");
                }
            }
        }

        if (strlen($password) < 10) { // password trop court
            throw new AuthnException("Mot de passe trop court (10 caractères minimum)");
        }

        $password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);

        $user = new User($username, $email);
        $user = $r->inscription($user, $password, $role);

        self::setPreferences($user);

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

    public static function setPreferences(User $user): void
    {
        $r = NRVRepository::getInstance();
        if (isset($_COOKIE['preferences'])){
            $preferences = unserialize($_COOKIE['preferences']);
            $userPrefs = $r->getPreferencesFromUserId($user->id);
            foreach($preferences as $pref){
                if (!in_array($pref, $userPrefs)) {
                    $r->ajouterPreference($user->id, $pref);
                }
            }
            setcookie('preferences', '', time() - 3600);
        }

    }
}