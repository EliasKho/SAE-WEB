<?php

namespace iutnc\nrv\exception;

/**
 * Class AuthnException, gère les exceptions liées à l'authentification, l'inscription et la déconnexion
 */
class AuthnException extends \Exception{
    /**
     * methode statique qui retourne un message d'erreur si l'utilisateur n'est pas connecté, avec un lien pour se connecter
     * @param string $message
     */
    public static function notSignedIn(): AuthnException
    {
        return new AuthnException("<p>Vous n'êtes pas connecté. <a href='index.php?action=sign-in'>Connectez-vous</a> pour accéder à cette page!</p>");
    }
}