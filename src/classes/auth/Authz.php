<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthorizationException;
use iutnc\nrv\user\User;

/**
 * Classe permettant de vérifier les autorisations d'un utilisateur avant une action
 */
class Authz {
    // Utilisateur à vérifier
    private User $user;

    /**
     * Constructeur
     * @param User $user Utilisateur à vérifier
     */
    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * Vérifie si l'utilisateur est à le rôle nécessaire
     */
    public function checkRole(int $role) {
        // Si l'utilisateur n'a pas le rôle nécessaire, on lance une exception
        // hiérarchie des rôles, plus le rôle est élevé, plus il a de droits
        if ($this->user->role < $role) {
            throw new AuthorizationException("Accès refusé");
        }
    }
}