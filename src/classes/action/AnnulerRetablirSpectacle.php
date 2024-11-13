<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\action\Spectacles;
use iutnc\nrv\action as ACT;


class AnnulerRetablirSpectacle extends Action
{
    protected function executeGet(): string
    {
        $idSpectacle = $_GET['idSpectacle'] ?? null;
        if (!$idSpectacle) {
            return "ID du spectacle manquant.";
        }

        // Récupérer le spectacle depuis le dépôt
        $repository = NRVRepository::getInstance();
        $spectacle = $repository->getSpectacleFromId($idSpectacle);

        if ($spectacle) {
            // Basculer l'état d'annulation
            if ($spectacle->estAnnule) {
                $spectacle->retablir();
                $message = "Le spectacle a été rétabli.";
            } else {
                $spectacle->annuler();
                $message = "Le spectacle a été annulé.";
            }

            // Enregistrer la mise à jour dans la base de données
            $repository->updateEtatSpectacle($spectacle);

            echo "<script>window.onload = ()=>{window.alert('$message');};</script>";
            $act = new ACT\Spectacles();
            return $act->executeGet();
        }

        return "Spectacle introuvable.";
    }

    protected function executePost(): string
    {
        return $this->executeGet();
    }
}
