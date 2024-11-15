<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

/**
 * Classe qui affiche tous les spectacles
 * @package iutnc\nrv\action
 */
class Spectacles extends Action {

    /**
     * methode qui affiche tous les spectacles et un formulaire qui propose un filtre
     */
    protected function executeGet(): string {
        // On récupère tous les spectacles
        $r = NRVRepository::getInstance();
        $spectacles = $r->getAllSpectacles();
        // on utilise une méthode pour créer le formulaire
        $html = $this->form($r);
        // on affiche les spectacles
        foreach ($spectacles as $spectacle) {
            $render = new SpectacleRender($spectacle);
            $html .= "<br>".$render->renderCompact();
        }
        // on retourne le html
        return $html;
    }

    /**
     * Méthode qui gère les filtres effectués par le formulaire
     */
    protected function executePost(): string{
        // on récupère le repository
        $r = NRVRepository::getInstance();
        // on utilise une méthode pour créer le formulaire
        $html = $this->form($r);
        // on récupère les valeurs du formulaire
        $date = "";
        $style = "";
        $lieu = "";
        if (isset($_POST['date'])){
            $date = $_POST['date'];
        }
        if (isset($_POST['style'])){
            $style = $_POST['style'];
        }
        if (isset($_POST['lieu'])){
            $lieu = $_POST['lieu'];
        }

        // on récupère les spectacles filtrés
        $spectacles = $r->getSpectaclesFiltres($date, $style, $lieu);
        // on affiche un message si aucun spectacle n'est trouvé
        if ($spectacles == null) {
            $html .= "<p>Aucun spectacle trouvé.</p>";
        }
        // sinon on affiche les spectacles
        else {
            foreach ($spectacles as $spectacle) {
                $render = new SpectacleRender($spectacle);
                $html .= "<br>".$render->renderCompact();
            }
        }
        return $html;
    }


    /**
     * Méthode qui crée un formulaire pour filtrer les spectacles
     * @param NRVRepository $r
     * @return string : le formulaire
     */
    protected function form(NRVRepository $r): string{
        // on crée le formulaire, on peut filtrer par date, style et lieu
        $html = <<<FIN
        <h3 class="centre">Tri :</h3>
        <form class="centre" method="post" action="?action=spectacles">
            <label for="date">Date :</label>
            <input type="date" name="date" id="date">
            <label for="style">Style :</label>
            <select name="style" id="style">
                <option value="" selected>Selectionnez un style</option>
        FIN;
        $c = 0;
        // on récupère tous les styles et lieux
        $styles=$r->getAllStyles();
        foreach ($styles as $style) {
            $c++;
            $html .= "<option value='$c'>{$style}</option>";
        }
        $html .= <<<FIN
            </select>
            <label for="lieu">Lieu :</label>
            <select name="lieu" id="lieu">
                <option value="" selected>Selectionnez un lieu</option>
        FIN;
        $c = 0;
        $lieux = $r->getAllLieux();
        foreach ($lieux as $lieu) {
            $c++;
            $html .= "<option value='$c'>{$lieu}</option>";
        }
        $html .= <<<FIN
            </select>
            <input type="submit" value="Trier">
        </form>
        FIN;
        return $html;
    }
}