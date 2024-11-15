<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthorizationException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\action\DisplayAllSpectacles;
use iutnc\nrv\action as ACT;
use iutnc\nrv\user\User;

/**
 * Action permettant d'annuler un spectacle. Classe séparée de RetablirSpectacle pour éviter de rétablir lors d'un refresh de page
 */
class AnnulerSpectacle extends Action
{
    /**
     * Exécute l'action.
     *
     * @return string Le contenu de la page à afficher.
     */
    protected function executeGet(): string
    {
        // Vérifier que l'utilisateur est connecté et a le rôle nécessaire
        try {
            $authz = new AuthZ(AuthnProvider::getSignedInUser());
            $authz->checkRole(User::$STAFF);
        } catch (AuthorizationException $e) {
            return $e->getMessage();
        }
        // Récupérer l'ID du spectacle dans l'URL
        $idSpectacle = $_GET['idSpectacle'] ?? null;
        if (!$idSpectacle) {
            return "ID du spectacle manquant.";
        }

        // Récupérer le spectacle depuis le dépôt
        $repository = NRVRepository::getInstance();
        $spectacle = $repository->getSpectacleFromId($idSpectacle);

        if ($spectacle) {
            // Annuler le spectacle si il n'est pas déjà annulé
            if (!$spectacle->estAnnule) {
                $spectacle->changerAnnulation();
                $message = "Le spectacle a été annulé.";
                // Enregistrer la mise à jour dans la base de données
                $repository->updateSpectacle($spectacle);
                // Afficher un message de confirmation
                echo "<script>window.onload = ()=>{window.alert('$message');};</script>";
            }
            // Rediriger vers la liste des spectacles
            $act = new ACT\DisplayAllSpectacles();
            return $act();
        }
        return "Spectacle introuvable.";
    }

    protected function executePost(): string
    {
        return $this->executeGet();
    }
}
