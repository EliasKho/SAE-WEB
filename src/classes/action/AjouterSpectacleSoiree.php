<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthorizationException;
use iutnc\nrv\render\SoireeRender;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;

/**
 * Action permettant d'ajouter un spectacle à une soirée
 */
class AjouterSpectacleSoiree extends Action {

    /**
     * formulaire pour ajouter un spectacle à une soirée
     */
    protected function executeGet(): string {
        // Vérification des droits, seul le staff peut ajouter un spectacle à une soirée
        try {
            $authz = new AuthZ(AuthnProvider::getSignedInUser());
            $authz->checkRole(User::$STAFF);
        } catch (AuthorizationException $e) {
            return $e->getMessage();
        }

        // l'id de la soiree est passé dans l'url
        if (!isset($_GET['idSoiree'])){
            return 'Soiree non trouvée';
        }
        // récupération de la soiree
        $idSoiree = filter_var($_GET['idSoiree'], FILTER_SANITIZE_NUMBER_INT);
        $r = NRVRepository::getInstance();
        // récupération du titre de la soiree
        $soiree = $r->getSoireeFromId($idSoiree);
        $titre = $soiree->nomSoiree;

        $html = <<<HTML
            <h2>Ajouter un spectacle à la soirée : {$titre}</h2>
            <form method="post" action="?action=ajouter-spec-soiree">
                <input type="hidden" name="idsoiree" value="{$idSoiree}">
                <label for="idspectacle">Spectacle :</label>
                <select name="idspectacle" id="idspectacle">
        HTML;
        // liste des spectacles pouvant être ajoutés
        $spectacles = $r->getAllSpectacles();
        foreach ($spectacles as $spectacle) {
            $html .= "<option value='{$spectacle->idSpectacle}'>{$spectacle->titre}</option>";
        }
        $html .= <<<HTML
                </select>
                <button type="submit">Ajouter</button>
            </form>
        HTML;
        return $html;
    }

    /**
     * Ajout du spectacle à la soirée
     */
    protected function executePost(): string {
        // Vérification des droits, seul le staff peut ajouter un spectacle à une soirée
        try {
            $authz = new AuthZ(AuthnProvider::getSignedInUser());
            $authz->checkRole(User::$STAFF);
        } catch (AuthorizationException $e) {
            return $e->getMessage();
        }
        if (!isset($_POST['idsoiree']) || !isset($_POST['idspectacle'])){
            return 'Paramètres manquants';
        }
        // récupération de l'id de la soiree et du spectacle
        $r = NRVRepository::getInstance();
        $html="";
        $idSoiree = filter_var($_POST['idsoiree'], FILTER_SANITIZE_NUMBER_INT);
        $idSpectacle = filter_var($_POST['idspectacle'], FILTER_SANITIZE_NUMBER_INT);
        // vérification que le spectacle n'est pas déjà présent dans la soirée
        $spectacles = $r->getSpectaclesFromSoireeId($idSoiree);
        // si le spectacle est déjà présent, on affiche un message d'erreur
        foreach ($spectacles as $spectacle) {
            if ($spectacle->idSpectacle == $idSpectacle) {
                $html.= "Spectacle déjà présent dans la soirée,  <a href='?action=ajouter-spec-soiree&idSoiree={$idSoiree}'>veuillez réessayer</a><br><br>";
                break;
            }
        }
        // si le spectacle n'est pas déjà présent, on l'ajoute à la soirée
        if ($html == "") {
            // ajout du spectacle à la soirée
            $r->lierSpectacleSoiree($idSoiree, $idSpectacle);
            $html.= '<h3>Spectacle ajouté à la soirée avec succès</h3><br><br>';
            $sr = new SoireeRender($r->getSoireeFromId($idSoiree));
            $html.= $sr->renderFull();
        }
        return $html;
    }
}