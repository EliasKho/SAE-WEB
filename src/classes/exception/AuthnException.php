<?php

namespace iutnc\nrv\exception;

class AuthnException extends \Exception
{
    public static function notSignedIn(): AuthnException
    {
        return new AuthnException("<p>Vous n'êtes pas connecté. <a href='index.php?action=sign-in'>Connectez-vous</a> pour accéder à cette page!</p>");
    }
}