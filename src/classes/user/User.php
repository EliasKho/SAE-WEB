<?php

namespace iutnc\nrv\user;

use iutnc\nrv\exception\CompteException;

/**
 * Classe User : représente un utilisateur du site
 */
class User{
    // Constantes pour les rôles des utilisateurs
    public static int $STANDARD = 1;
    public static int $STAFF = 2;
    public static int $ADMIN = 3;

    // Identifiant de l'utilisateur
    protected int $id;
    // Nom d'utilisateur
    protected string $username;
    // Adresse email de l'utilisateur
    protected string $email;
    // Rôle de l'utilisateur
    protected int $role;

    /**
     * constructeur de compte utilisateur par défaut avec un rôle standard
     * @param string $username
     * @param string $email
     * @param int $role
     */
    public function __construct(string $username, string $email, int $role = 1) {
        // Par défaut, l'utilisateur n'a pas d'identifiant
        $this->id = 0;
        // Vérification des paramètres
        if (strlen($username) > 50) {
            throw new CompteException("Nom d\'utilisateur trop long (max 50 caractères)");
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            throw new CompteException("Email non valide ou trop long (max 100 caractères)");
        } else {
            // si tout est bon, on initialise les propriétés
            $this->username = $username;
            $this->email = $email;
            $this->role = $role;
        }
    }

    /**
     * Méthode magique __get pour récupérer les propriétés protégées
     * @param string $at Nom de la propriété à récupérer
     * @return mixed Valeur de la propriété
     * @throws \Exception Si la propriété est invalide
     */
    public function __get(string $at): mixed{
        // Vérification de l'existence de la propriété
        if (property_exists($this, $at)) {
            // Renvoi de la propriété
            return $this->$at;
        } else {
            // Si la propriété n'existe pas, on lève une exception
            throw new \Exception("$at: propriété invalide");
        }
    }

    /**
     * Méthode pour modifier l'id de l'utilisateur
     */
    public function setId(int $id) {
        $this->id = $id;
    }

    /**
     * Méthode pour modifier le rôle de l'utilisateur
     */
    public function setRole(int $role) {
        // Vérification de la validité du rôle
        if (!in_array($role, [User::$STANDARD,User::$STAFF,User::$ADMIN])) {
            echo '<script>window.alert("Rôle invalide")</script>';
        }
        // Si tout est bon, on modifie le rôle
        else{
            $this->role = $role;
        }
    }
}
