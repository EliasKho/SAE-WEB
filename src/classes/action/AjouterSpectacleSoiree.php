<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\SoireeRender;
use iutnc\nrv\repository\NRVRepository;

class AjouterSpectacleSoiree extends Action {
    protected function executeGet(): string {
        if (!isset($_GET['idSoiree'])){
            return 'Soiree non trouvée';
        }
        $idSoiree = filter_var($_GET['idSoiree'], FILTER_SANITIZE_NUMBER_INT);
        $r = NRVRepository::getInstance();
        $soiree = $r->getSoireeById($idSoiree);
        $titre = $soiree->nomSoiree;

        $html = <<<HTML
            <h2>Ajouter un spectacle à la soirée : {$titre}</h2>
            <form method="post" action="?action=ajouter-spec-soiree">
                <input type="hidden" name="idsoiree" value="{$idSoiree}">
                <label for="idspectacle">Spectacle :</label>
                <select name="idspectacle" id="idspectacle">
        HTML;
        $spectacles = $r->getAllSpectacles();
        foreach ($spectacles as $spectacle) {
            $html .= "<option value='{$spectacle->idSpectacle}'>{$spectacle->titre}</option>";
        }
        $html .= <<<HTML
                </select>
                <button type="submit" class="button">Ajouter</button>
            </form>
        HTML;
        return $html;
    }

    protected function executePost(): string {
        if (!isset($_POST['idsoiree']) || !isset($_POST['idspectacle'])){
            return 'Paramètres manquants';
        }
        $r = NRVRepository::getInstance();
        $html="";
        $idSoiree = filter_var($_POST['idsoiree'], FILTER_SANITIZE_NUMBER_INT);
        $idSpectacle = filter_var($_POST['idspectacle'], FILTER_SANITIZE_NUMBER_INT);
        $spectacles = $r->getSpectaclesBySoiree($idSoiree);
        foreach ($spectacles as $spectacle) {
            if ($spectacle->idSpectacle == $idSpectacle) {
                $html.= "Spectacle déjà présent dans la soirée,  <a href='?action=ajouter-spec-soiree&idSoiree={$idSoiree}'>veuillez réessayer</a><br><br>";
                break;
            }
        }
        if ($html == "") {
            $r->ajouterSpectacleSoiree($idSoiree, $idSpectacle);
            $html.= '<h3>Spectacle ajouté à la soirée avec succès</h3><br><br>';
            $sr = new SoireeRender($r->getSoireeById($idSoiree));
            $html.= $sr->renderFull();
        }
        return $html;
    }
}