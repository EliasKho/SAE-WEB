<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthorizationException;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;

/**
 * Action permettant d'ajouter une soirée
 */
class AjouterSoiree extends Action{

    /**
     * Renvoie le formulaire d'ajout d'une soirée
     * @return string
     */
    public function executeGet() : string{
        // Vérification des droits, seul le staff peut ajouter une soiree
        try {
            $authz = new AuthZ(AuthnProvider::getSignedInUser());
            $authz->checkRole(User::$STAFF);
        } catch (AuthorizationException $e) {
            return $e->getMessage();
        }
        // on récupère le repository
        $r = NRVRepository::getInstance();

        // on récupère les lieux pour pouvoir choisir un lieu existant
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
        // on affiche les lieux existants pour choisir
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

    /**
     * Ajoute la soirée dans la base de données
     * @return string
     */
    public function executePost() : string{
        // Vérification des droits, seul le staff peut ajouter une soiree
        try {
            $authz = new AuthZ(AuthnProvider::getSignedInUser());
            $authz->checkRole(User::$STAFF);
        } catch (AuthorizationException $e) {
            return $e->getMessage();
        }
        // on récupère les informations du formulaire
        $titre = $_POST["titre"];
        $thematique = $_POST["thematique"];
        $date = $_POST["date"];
        $horaire = $_POST["horaire"];
        $tarif = $_POST["tarif"];
        $lieu = $_POST["lieu"];
        // on récupère le repository
        $r = NRVRepository::getInstance();
        // on ajoute la soirée
        $r->ajouterSoiree($titre, $thematique, $date, $horaire, $tarif, $lieu);
        // on renvoie une confirmation de l'ajout
        return "Soirée ajoutée";
    }
}