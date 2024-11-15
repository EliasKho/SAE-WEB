<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\render\SoireeRender;
use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;

/**
 * Action permettant de modifier les spectacles d'une soirée.
 */
class ModifierSpectacleSoiree extends Action{
    /**
     * Affiche la liste des spectacles d'une soirée et permet d'en ajouter ou d'en supprimer un
     * @return string
     */
    public function executeGet() : string{
        // Vérification des droits
        $r = NRVRepository::getInstance();
        $user = AuthnProvider::getSignedInUser();
        $authz = new Authz($user);
        $authz->checkRole(User::$STAFF);

        // Récupération de la soirée dans l'URL
        $idSoiree = $_GET['idSoiree'] ?? null;
        if (!$idSoiree) {
            return "ID de la soirée manquant.";
        }
        // Récupération de la soirée
        $soiree = $r->getSoireeFromId($idSoiree);
        if (!$soiree) {
            return "Soirée introuvable.";
        }
        // Récupération des spectacles de la soirée
        $titre = htmlspecialchars($soiree->nomSoiree);
        $spectacles = $r->getSpectaclesFromSoireeId($idSoiree);

        // bouton pour ajouter un spectacle
        $btnAdd = "<a href='index.php?action=ajouter-spec-soiree&idSoiree={$idSoiree}' class='button'>Ajouter un spectacle</a>";

        $html = <<<FIN
            <h1>Modifier la soirée {$titre}</h1>
            <h3>Liste des spectacles : </h3> 
            <ul>
FIN;

        // pour tous les spectacles de la soirée
        foreach ($spectacles as $spectacle) {
            $id = $spectacle->idSpectacle;
            // on affiche le titre et un bouton pour supprimer le spectacle
            $html .= <<<FIN
            <li>{$spectacle->titre}
            <form method='post' action='?action=modifier-soiree'>
            <input type='hidden' name='idSoiree' value='{$idSoiree}'>
            <input type='hidden' name='idSpec' value = {$id}>
            <button type='submit'>Supprimer</button>
            </form>
            </li>
FIN;
        }
        $html .= <<<FIN
                </ul>   
                </br>
                {$btnAdd}              
FIN;
        return $html;
    }

    /**
     * Supprime un spectacle d'une soirée
     * @return string
     */
    public function executePost() : string{
        // Vérification des droits
        $r = NRVRepository::getInstance();
        $user = AuthnProvider::getSignedInUser();
        $authz = new Authz($user);
        $authz->checkRole(User::$STAFF);

        // Récupération des paramètres
        $idSoiree = $_POST['idSoiree'] ?? null;
        $idSpec = $_POST['idSpec'] ?? null;
        if (!$idSoiree || !$idSpec) {
            return "ID de la soiree ou du spectacle manquant.";
        }
        // Suppression du spectacle de la soirée dans la base de données
        $r->deleteSpectacleSoiree($idSpec, $idSoiree);
        // Affichage de la soirée une fois le spectacle supprimé
        $html = "<h3>Spectacle supprimé de la soirée.</h3><br><br>";
        $sr = new SoireeRender($r->getSoireeFromId($idSoiree));
        $html .= $sr->renderFull();
        return $html;
    }
}