<?php

namespace iutnc\nrv\action;

use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\repository\NRVRepository;

/**
 * Action permettant de modifier un spectacle.
 */
class ModifierSpectacle extends Action{

    /**
     * Récupère les informations du spectacle à modifier et les affiche dans un formulaire.
     * @return string Le formulaire HTML.
     */
    public function executeGet(): string{
        // Récupérer l'ID du spectacle à modifier depuis l'URL
        $idSpectacle = $_GET['idSpectacle'] ?? null;
        if (!$idSpectacle) {
            return "ID du spectacle manquant.";
        }
        // Récupérer le spectacle à modifier
        $repository = NRVRepository::getInstance();
        $spectacle = $repository->getSpectacleFromId($idSpectacle);
        // Vérifier que le spectacle existe
        if (!$spectacle) {
            return "Spectacle introuvable.";
        }
        // Préremplir le formulaire avec les données existantes du spectacle
        $titre = htmlspecialchars($spectacle->titre);
        $description = htmlspecialchars($spectacle->description);
        $horaire = htmlspecialchars($spectacle->horaireSpec);
        $duree = htmlspecialchars($spectacle->dureeSpec);
        $video = htmlspecialchars($spectacle->video);
        $styles = $repository->getAllStyles();

        // Récupérer tous les artistes et ceux associés au spectacle
        $allArtistes = $repository->getAllArtistes(); // Retourne les noms des artistes
        $spectacleArtistes = $repository->getArtistesFromSpectacleId($idSpectacle); // Retourne les noms des artistes associés

        $html = <<<FIN
            <h1>Modifier le spectacle</h1>
            <form method="post" action="?action=ModifSpectacle&idSpectacle={$idSpectacle}" enctype="multipart/form-data">
                <label for="titre">Titre du spectacle:</label>
                <input type="text" id="titre" name="titre" value="{$titre}" required>      
                </br></br>                    
                
                <label for="horaire">Horaire du spectacle:</label>
                <input type="time" id="horaire" name="horaire" value="{$horaire}" required>
                </br></br>
                
                <label for="duree">Durée du spectacle:</label>
                <input type="number" id="duree" name="duree" value="{$duree}" required>
                </br></br>
                
                <label for="description">Description du spectacle:</label>
                <textarea id="description" name="description" required>{$description}</textarea>
                </br></br>
                
                <label for='style'>Style du spectacle:</label>
                <select id='style' name='style' required>
        FIN;

        // Ajouter les styles avec option sélectionnée pour le style du spectacle
        foreach ($styles as $key => $styleName) {
            $selected = ($key + 1 == $spectacle->idStyle) ? "selected" : "";
            $html .= "<option value='" . ($key + 1) . "' {$selected}>{$styleName}</option>";
        }

        // Ajouter les artistes avec cases à cocher et précocher ceux associés au spectacle
        $html .= "</select></br></br><label>Artistes du spectacle:</label>";
        foreach ($allArtistes as $artiste) {
            $artisteNom = htmlspecialchars($artiste['nomArtiste']);
            $id = $artiste['idArtiste'];
            $checked = in_array($artisteNom, $spectacleArtistes) ? "checked" : "";
            $html .= "<label><input type='checkbox' name='artistes[]' value='{$id}' {$checked}> {$artisteNom}</label> ";
        }

        $html .= <<<FIN
                </br></br>
                <label for="images">Images du spectacle:</label>
                <input type="file" id="images" name="images[]" multiple>
                </br></br>
                
                <label for="video">Vidéo du spectacle (lien vers la vidéo):</label>
                <input type="text" id="video" name="video" value="{$video}" required>       
                </br></br>       

                <input type="submit" value="Modifier">
            </form>
        FIN;
        return $html;
    }

    /**
     * Modifie le spectacle avec les informations reçues du formulaire.
     * @return string Message de confirmation.
     */
    public function executePost(): string
    {
        // Récupérer l'ID du spectacle à modifier depuis l'URL
        $idSpectacle = $_GET['idSpectacle'] ?? null;
        if (!$idSpectacle) {
            return "ID du spectacle manquant.";
        }
        // Récupérer les informations du formulaire
        $r = NRVRepository::getInstance();
        $annuler = (bool)$r->getSpectacleFromId($idSpectacle)->estAnnule;
        $titre = $_POST['titre'];
        $horaire = $_POST['horaire'];
        $duree = $_POST['duree'];
        $description = $_POST['description'];
        $style = $_POST['style'];
        $images = $_FILES['images'];
        $video = $_POST['video'];
        $selectedArtistes = $_POST['artistes'] ?? []; // Liste des artistes sélectionnés

        // Créer un objet Spectacle avec les informations reçues
        $spectacle = new Spectacle($titre, $description, $video, $horaire, $duree, $style, $annuler, $idSpectacle);

        // Mettre à jour le spectacle dans la base de données
        $r->updateSpectacle($spectacle);
        // Mettre à jour les images et les artistes associés au spectacle
        $r->updateSpectacleImages($idSpectacle, $images);
        $r->updateSpectacleArtistes($idSpectacle, $selectedArtistes);
        // Retourner un message de confirmation
        return "Spectacle modifié avec succès.";
    }
}
