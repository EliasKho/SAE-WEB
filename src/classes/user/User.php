<?php

namespace iutnc\nrv\user;

use iutnc\nrv\exception\CompteException;

class User
{
    public static int $STANDARD = 1;
    public static int $STAFF = 2;
    public static int $ADMIN = 3;

    protected int $id;

    // Nom d'utilisateur
    protected string $username;

    // Adresse email de l'utilisateur
    protected string $email;

    // Rôle de l'utilisateur
    protected int $role;

    /**
     * constructeur de compte utilisateur
     * @param string $username
     * @param string $email
     * @param int $role
     */
    public function __construct(string $username, string $email) {
        $this->id = 0;
        if (strlen($username) > 50) {
            throw new CompteException("Nom d\'utilisateur trop long (max 50 caractères)");
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            throw new CompteException("Email non valide ou trop long (max 100 caractères)");
        } else {
            $this->username = $username;
            $this->email = $email;
            $this->role = User::$STANDARD;
        }
    }

    /**
     * Méthode magique __get pour récupérer les propriétés protégées
     * @param string $at Nom de la propriété à récupérer
     * @return mixed Valeur de la propriété
     * @throws \Exception Si la propriété est invalide
     */
    public function __get(string $at): mixed
    {
        if (property_exists($this, $at)) {
            return $this->$at;
        } else {
            throw new \Exception("$at: propriété invalide");
        }
    }

    public function setId(int $id) {
        $this->id = $id;
    }

    public function setRole(int $role) {
        if (!in_array($role, [User::$STANDARD,User::$STAFF,User::$ADMIN])) {
            echo '<script>window.alert("Rôle invalide")</script>';
        }
        $this->role = $role;
    }
}
