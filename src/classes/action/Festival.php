<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\SpectacleRender;
use iutnc\nrv\repository\NRVRepository;

class Festival extends Action {

    protected function executeGet(): string {
        $r = NRVRepository::getInstance();
        $spectacles = $r->getAllSpectacles();
//        var_dump($spectacles);
        $html = <<<FIN
        <h3>Tri :</h3>
        <form method="post" action="?action=festival">
            <label for="date">Date :</label>
            <input type="date" name="date" id="date">
            
            <label for="style">Style :</label>
            <select name="style" id="style">
                <option value="1">Rock</option>
                <option value="2">Blues</option>
                <option value="3">Jazz</option>
                <option value="4">Metal</option>
                <option value="5">Pop</option>
            </select>
            <input type="submit" value="Trier">
        </form>
        FIN;
        foreach ($spectacles as $spectacle) {
            $render = new SpectacleRender($spectacle);
            $html .= $render->renderCompact();
        }
        return $html;
    }

    protected function executePost(): string{
        $html = <<<FIN
            <h3>Tri :</h3>
            <form method="post" action="?action=festival">
                <label for="date">Date :</label>
                <input type="date" name="date" id="date">
                
                <label for="style">Style :</label>
                <select name="style" id="style">
                    <option value="1">Rock</option>
                    <option value="2">Blues</option>
                    <option value="3">Jazz</option>
                    <option value="4">Metal</option>
                    <option value="5">Pop</option>
                </select>
                <input type="submit" value="Trier">
            </form>
            FIN;
        if (isset($_POST['date'])) {
            if (isset($_POST['style'])) {
                $r = NRVRepository::getInstance();
                $spectacles = $r->getSpectaclesByTri($_POST['date'], $_POST['style']);
                if ($spectacles == null) {
                    $html .= "<p>Aucun spectacle trouvé à cette date.</p>";
                }
                else {
                    foreach ($spectacles as $spectacle) {
                        $render = new SpectacleRender($spectacle);
                        $html .= $render->renderCompact();
                    }
                }
            }
            else {
                $r = NRVRepository::getInstance();
                $spectacles = $r->getSpectaclesByDate($_POST['date']);
                if ($spectacles == null) {
                    $html .= "<p>Aucun spectacle trouvé à cette date.</p>";
                }
                else {
                    foreach ($spectacles as $spectacle) {
                        $render = new SpectacleRender($spectacle);
                        $html .= $render->renderCompact();
                    }
                }
            }
        }
        if (!isset($_POST['style'])) {
            return "Veuillez renseigner un style.";
        }

        return $html;
    }
}