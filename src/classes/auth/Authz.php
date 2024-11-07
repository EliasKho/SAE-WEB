<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthorizationException;
use iutnc\nrv\user\User;

class Authz
{
    private User $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * Vérifie si l'utilisateur est à le rôle nécessaire
     * @return bool
     */
    public function checkRole(int $role): bool
    {
        if ($this->user->role < $role) {
            throw new AuthorizationException("Accès refusé");
        }
        return true;
    }
}