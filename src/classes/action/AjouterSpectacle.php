<?php

namespace iutnc\nrv\action;

use iutnc\nrv\repository\NRVRepository;

class AjouterSpectacle extends Action{

    public function executeGet() : string{
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
        for ($i = 1; $i <= count($artistes); $i++) {
            $html.="<label><input type='checkbox' id='artistes' name='artistes[]' value='$i'>". $artistes[$i-1] ."</label>";
        }
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

    public function executePost() : string{
        $uploadDir = 'img/';

        $titre = $_POST['titre'];
        $horaire = $_POST['horaire'];
        $duree = $_POST['duree'];
        $description = $_POST['description'];
        $style = $_POST['style'];
        $images = $_FILES['images'];
        $video = $_POST['video'];
        $artistes = $_POST['artistes'];
        
        $r = NRVRepository::getInstance();
        $spectacle = $r->ajouterSpectacle($titre, $horaire, $duree, $description, $style, $images, $video);
        $idSpec = $spectacle->idSpectacle;

        for($i = 0; $i < count($images['name']); $i++){
            // Vérification des erreurs d'upload
            if ($images['error'][$i] !== UPLOAD_ERR_OK) {
                return "Erreur lors de l'upload du fichier.". $images['error'][$i];
            }

            // Récupération du nom et chemin temporaire du fichier
            $nomFichier = $images['name'][$i];
            $tmpName = $images['tmp_name'][$i];

            // Vérification de l'extension du fichier
            $extension = pathinfo($nomFichier, PATHINFO_EXTENSION);

            $nomFichier = bin2hex(random_bytes(10)) . '.' . $extension;

            $idImage = $r->ajouterImage($nomFichier);

            // Nettoyage et déplacement du fichier
            $nomFichier = $uploadDir . basename($nomFichier);
            if (!move_uploaded_file($tmpName, $nomFichier)) {
                return "Erreur lors du déplacement du fichier.";
            }

            $r->lierSpectacleImage($idSpec, $idImage);
        }

        foreach($artistes as $artiste){
            if ($artiste == '') {
                continue;
            }
            $r->lierSpectacleArtiste($idSpec, $artiste);
        }

        return 'Spectacle ajouté';

    }
}