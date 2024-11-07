<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\user\User;

class Authz
{
    private User $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * Vérifie si l'utilisateur est un administrateur
     * @return bool
     */
    public function isAdmin(): bool {
        return $this->user->role === 'ADMIN';
    }

    public function isStaff(): bool {
        return $this->user->role === 'STAFF';
    }
}