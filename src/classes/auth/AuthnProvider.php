<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;

/**
 * Classe permettant de gérer l'authentification des utilisateurs (connexion, inscription, déconnexion)
 */
class AuthnProvider {

    /**
     * Permet de connecter un utilisateur
     * @param string $credential le nom d'utilisateur ou l'email de l'utilisateur
     * @param string $password le mot de passe de l'utilisateur
     * @throws AuthnException si le mot de passe est incorrect
     */
    public static function signin(string $credential, string $password): void
    {
        $r = NRVRepository::getInstance();
        // Si le credential correspond à un email, on récupère l'utilisateur correspondant dans la bd
        if (filter_var($credential, FILTER_VALIDATE_EMAIL)) {
            $user = $r->getUserFromMail($credential);
        } else {
            // Sinon, on récupère l'utilisateur correspondant au nom d'utilisateur
            $user = $r->getUserFromUsername($credential);
        }
        // On récupère le mot de passe de l'utilisateur
        $userPass = $r->getPasswordFromId($user->id);

        // On vérifie que le mot de passe entré correspond au mot de passe de l'utilisateur
        if (!password_verify($password, $userPass)) {
            // Si ce n'est pas le cas, on lève une exception
            throw new AuthnException("Mot de passe incorrect");
        }

        // Si tout est correct, on met à jour les préférences de l'utilisateur et on stocke les informations de session
        self::setPreferences($user);
        $_SESSION['user'] = serialize($user);
    }

    /**
     * Permet d'enregistrer un nouvel utilisateur
     * @param string $username
     * @param string $email
     * @param string $password
     * @param int $role
     * @throws AuthnException
     */
    public static function register(string $username, string $email, string $password,int $role)  // enregistre un nouvel utilisateur
    {
        $r = NRVRepository::getInstance();
        // On vérifie que l'email et le nom d'utilisateur ne sont pas déjà utilisés
        try {
            $r->getUserFromMail($email);
            throw new AuthnException("E");
        } catch (AuthnException $e) {
            if ($e->getMessage() == "E") {
                throw new AuthnException("Un utilisateur avec cet email existe déjà");
            }
        }
        // On vérifie que le nom d'utilisateur n'est pas déjà utilisé
        try{
            $r->getUserFromUsername($username);
            throw new AuthnException("U");
        } catch (AuthnException $e) {
            if ($e->getMessage() == "U") {
                throw new AuthnException("Un utilisateur avec ce nom d'utilisateur existe déjà");
            }
        }

        // Vérification du mot de passe
        if (strlen($password) < 10) { // password trop court
            throw new AuthnException("Mot de passe trop court (10 caractères minimum)");
        }

        // Hashage du mot de passe
        $password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);

        // Création de l'utilisateur
        $user = new User($username, $email);
        // Inscription de l'utilisateur dans la base de données
        $user = $r->inscription($user, $password, $role);

        // Mise à jour des préférences de l'utilisateur et stockage des informations de session
        self::setPreferences($user);
        $_SESSION['user'] = serialize($user);
    }

    /**
     * Permet de récupérer l'utilisateur connecté
     * @return User
     * @throws AuthnException si aucun utilisateur n'est connecté
     */
    public static function getSignedInUser(): User
    {
        // On vérifie qu'un utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            // Si ce n'est pas le cas, on lève une exception
            throw AuthnException::notSignedIn();
        }
        // On retourne l'utilisateur connecté
        return unserialize($_SESSION['user']);
    }

    /**
     * Permet de déconnecter l'utilisateur
     */
    public static function signout(): void
    {
        // On vérifie qu'un utilisateur est connecté
        AuthnProvider::getSignedInUser();
        // On détruit les informations de session
        unset($_SESSION['user']);
    }

    /**
     * Permet de mettre à jour les préférences de l'utilisateur lors de la connexion ou de l'inscription
     * @param User $user
     */
    public static function setPreferences(User $user): void {
        $r = NRVRepository::getInstance();
        // Si l'utilisateur a des préférences stockées dans un cookie, on les ajoute à la base de données
        if (isset($_COOKIE['preferences'])){
            // On récupère les préférences stockées dans le cookie
            $preferences = unserialize($_COOKIE['preferences']);
            // On récupère les préférences de l'utilisateur
            $userPrefs = $r->getPreferencesFromUserId($user->id);
            // Pour chaque préférence stockée dans le cookie, on l'ajoute à la base de données si elle n'est pas déjà présente
            foreach($preferences as $pref){
                if (!in_array($pref, $userPrefs)) {
                    $r->ajouterPreference($user->id, $pref);
                }
            }
            // On supprime le cookie
            setcookie('preferences', '', time() - 3600);
        }
    }
}