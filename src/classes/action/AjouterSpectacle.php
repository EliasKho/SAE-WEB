<?php

namespace iutnc\nrv\action;

use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthorizationException;
use iutnc\nrv\repository\NRVRepository;
use Exception;
use iutnc\nrv\user\User;

/**
 * Action permettant d'ajouter un spectacle.
 */
class AjouterSpectacle extends Action{

    /**
     * Formulaire d'ajout d'un spectacle
     */
    public function executeGet() : string{
        // Vérification des droits, seul le staff peut ajouter un spectacle
        try {
            $authz = new AuthZ(AuthnProvider::getSignedInUser());
            $authz->checkRole(User::$STAFF);
        } catch (AuthorizationException $e) {
            return $e->getMessage();
        }

        // Récupération des styles et artistes pour les formulaires
        $r = NRVRepository::getInstance();
        $styles = $r->getAllStyles();
        $artistes = $r->getAllArtistes();
        $html = "";
        $html.= <<<FIN
            <h1>Ajouter un spectacle</h1>
            <form method="post" action="?action=add-spectacle" enctype="multipart/form-data">
                <label for="titre">Titre du spectacle:</label>
                <input type="text" id="titre" name="titre" required>      
                </br></br>                    

                <label for="horaire">Horaire du spectacle:</label>
                <input type="time" id="horaire" name="horaire" required>
                </br></br>

                <label for="duree">Durée du spectacle:</label>
                <input type="number" id="duree" name="duree" required>
                </br></br>

                <label for="description">Description du spectacle:</label>
                <textarea id="description" name="description" required></textarea>
                </br></br>
        FIN;
        $html.= "<label for='style'>Style du spectacle:</label>";
        $html.= "<select id='style' name='style' required>";
        for ($i = 1; $i <= count($styles); $i++) {
            $html.= "<option value='$i'>".$styles[$i-1]."</option>";
        }
        $html.= <<<FIN
                </select>
                </br></br>
                
                <label for="images">Images du spectacle:</label>
                <input type="file" id="images" name="images[]" multiple required>
                </br></br>
                
                <label for="video">Vidéo du spectacle (lien vers la vidéo):</label>
                <input type="text" id="video" name="video" required>       
                </br></br>       
                
                <label>Artistes du spectacle:</label>
            FIN;
        // Affichage des artistes sous forme de checkbox
        foreach ($artistes as $artiste) {
            $artisteNom = htmlspecialchars($artiste['nomArtiste']);
            $id = $artiste['idArtiste'];
            $html.="<label><input type='checkbox' id='artistes' name='artistes[]' value='$id'>". $artisteNom ."</label>";
        }
        // création d'un petit script pour vérifier qu'au moins un artiste est sélectionné
        $html.= <<<FIN
                </select>
                </br></br>
                <input type="submit" onclick='return validateCheckboxes()' value="Ajouter">
                </form>


            <script>
            function validateCheckboxes() {
                // Sélectionne toutes les cases à cocher dans le groupe
                var checkboxes = document.querySelectorAll("input[type='checkbox']");
                // Vérifie si au moins une case est cochée
                for (var checkbox of checkboxes) {
                    if (checkbox.checked) return true;
                }
                // Alerte et empêche l'envoi du formulaire si aucune case n'est cochée
                alert("Veuillez sélectionner au moins un artiste.");
                return false;
            }
            </script>
            FIN;

        return $html;

    }

    /**
     * Ajout d'un spectacle dans la base de données
     */
    public function executePost() : string{
        // Vérification des droits, seul le staff peut ajouter un spectacle
        try {
            $authz = new AuthZ(AuthnProvider::getSignedInUser());
            $authz->checkRole(User::$STAFF);
        } catch (AuthorizationException $e) {
            return $e->getMessage();
        }

        // Récupération des données du formulaire
        $titre = $_POST['titre'];
        $horaire = $_POST['horaire'];
        $duree = $_POST['duree'];
        $description = $_POST['description'];
        $style = $_POST['style'];
        $images = $_FILES['images'];
        $video = $_POST['video'];
        $artistes = $_POST['artistes'];

        // on ajoute le spectacle dans la base de données
        $r = NRVRepository::getInstance();
        $spectacle = $r->ajouterSpectacle($titre, $horaire, $duree, $description, $style, $video);
        // on récupère l'id du spectacle ajouté
        $idSpec = $spectacle->idSpectacle;

        // On ajoute les images
        $r->updateSpectacleImages($idSpec, $images);

        // On ajoute les artistes associés au spectacle
        $r->updateSpectacleArtistes($idSpec, $artistes);
        // On retourne un message de succès
        return 'Spectacle ajouté';
    }
}