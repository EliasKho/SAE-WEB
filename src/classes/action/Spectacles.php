<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

class Spectacles extends Action {

    protected function executeGet(): string {
        $r = NRVRepository::getInstance();
        $spectacles = $r->getAllSpectacles();
        $styles=$r->getAllStyles();
        list($html, $style, $lieu) = $this->form($r, $styles);
        foreach ($spectacles as $spectacle) {
            $render = new SpectacleRender($spectacle);
            $html .= $render->renderCompact();
        }
        return $html;
    }

    protected function executePost(): string{
        $r = NRVRepository::getInstance();
        $styles=$r->getAllStyles();
        list($html, $style, $lieu) = $this->form($r, $styles);

//                <option value="1">Rock</option>
//                <option value="2">Blues</option>
//                <option value="3">Jazz</option>
//                <option value="4">Metal</option>
//                <option value="5">Pop</option>
//            </select>
//            <label for="lieu">Lieu :</label>
//            <input type="text" name="lieu" id="lieu">
//            <input type="submit" value="Trier">
//        </form>
//        FIN;
        $r = NRVRepository::getInstance();
        if (!isset($_POST['date']) ) {
            $date = "";
        } else {
            $date = $_POST['date'];
        }
        if (!isset($_POST['style']) ) {
            $style = "";
        } else {
            $style = $_POST['style'];
        }
        if (!isset($_POST['lieu']) ) {
            $lieu = "";
        } else {
            $lieu = $_POST['lieu'];
        }
        $spectacles = $r->getSpectaclesByTri($date, $style, $lieu);
        if ($spectacles == null) {
            $html .= "<p>Aucun spectacle trouvé.</p>";
        }
        else {
            foreach ($spectacles as $spectacle) {
                $render = new SpectacleRender($spectacle);
                $html .= $render->renderCompact();
            }
        }
        return $html;
    }

    /**
     * @param NRVRepository|null $r
     * @param array $styles
     * @return array
     */
    protected function form(?NRVRepository $r, array $styles): array
    {
        $lieux = $r->getAllLieux();
        $html = <<<FIN
        <h3>Tri :</h3>
        <form method="post" action="?action=spectacles">
            <label for="date">Date :</label>
            <input type="date" name="date" id="date">
            <label for="style">Style :</label>
            <select name="style" id="style">
                <option value="" selected>Selectionnez un style</option>
        FIN;
        $c = 0;
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
        foreach ($lieux as $lieu) {
            $c++;
            $html .= "<option value='$c'>{$lieu}</option>";
        }
        $html .= <<<FIN
            </select>
            <input type="submit" value="Trier">
        </form>
        FIN;
        return array($html, $style, $lieu);
    }
}