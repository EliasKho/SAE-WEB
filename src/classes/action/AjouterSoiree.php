<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NRVRepository;

class AjouterSoiree extends Action{
    public function executeGet() : string{
        $r = NRVRepository::getInstance();
        $lieux = $r->getAllLieux();
        $html = "";
        $html.= <<<FIN
            <h1>Ajouter une soirée</h1>
            <form method="post" action="?action=add-soiree">
            
                <label for="titre">Titre de la soirée:</label>
                <input type="text" id="titre" name="titre" required>              
                </br></br>
                
                <label for="thematique">Thematique:</label>
                <input type="text" id="thematique" name="thematique" required>
                </br></br>                             
            
                <label for="date">Date de la soirée:</label>
                <input type="date" id="date" name="date" required>      
                </br></br>                    

                <label for="horaire">Horaire de la soirée:</label>
                <input type="time" id="horaire" name="horaire" required>
                </br></br>
                
                <label for="tarif">Tarif de la soirée:</label>
                <input type="number" id="tarif" name="tarif" step="0.01" min="0" required>
                </br></br>     
        FIN;
        $html.= "<label for='lieu'>Lieu de la soirée:</label>";
        $html.= "<select id='lieu' name='lieu' required>";
        for ($i = 1; $i <= count($lieux); $i++) {
            $html.= "<option value='$i'>".$lieux[$i-1]."</option>";
        }
        $html.= <<<FIN
                <input type="submit" value="Ajouter">
                </form>
        FIN;
        return $html;
    }

    public function executePost() : string{
        $titre = $_POST["titre"];
        $thematique = $_POST["thematique"];
        $date = $_POST["date"];
        $horaire = $_POST["horaire"];
        echo ($_POST["tarif"]);
        $tarif = $_POST["tarif"];
        echo $tarif;
        $lieu = $_POST["lieu"];
        $r = NRVRepository::getInstance();
        $r->ajouterSoiree($titre, $thematique, $date, $horaire, $tarif, $lieu);
        return "Soirée ajoutée";
    }
}