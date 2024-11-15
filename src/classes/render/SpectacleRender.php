<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;
use iutnc\nrv\exception\AuthnException;

/**
 * Classe SpectacleRender
 * Cette classe permet de générer le code HTML pour afficher un spectacle sous différentes formes
 */
class SpectacleRender{
    // Le spectacle à afficher
    private Spectacle $spectacle;

    /**
     * Constructeur de la classe SpectacleRender
     * @param Spectacle $spectacle Le spectacle à afficher
     */
    public function __construct(Spectacle $spectacle)
    {
        $this->spectacle = $spectacle;
    }

    /**
     * Méthode renderCompact
     * Cette méthode permet de générer le code HTML pour afficher un spectacle de manière compacte
     * @return string Le code HTML généré
     */
    public function renderCompact(): string
    {
        //Affichage compact d’un spectacle : titre, image, horaire, durée
        $id=filter_var($this->spectacle->idSpectacle, FILTER_SANITIZE_NUMBER_INT);
        $image = filter_var($this->spectacle->images[0], FILTER_SANITIZE_URL);
        $titre = filter_var($this->spectacle->titre, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaire = filter_var($this->spectacle->horaireSpec, FILTER_SANITIZE_SPECIAL_CHARS);
        $duree = filter_var($this->spectacle->dureeSpec, FILTER_SANITIZE_SPECIAL_CHARS);
        $duree = intval($duree);
        $heure = floor($duree/60);
        $min = $duree%60;
        if($min<10)
            $min = "0".$min;

        // Vérifier si le spectacle est annulé
        $estAnnule = filter_var($this->spectacle->estAnnule, FILTER_VALIDATE_BOOLEAN);
        $btnAnnuler = "";
        $btnModifSpec = "";
        // Définir le bouton "Ajouter à mes préférences"
        $btnPref = "<a href='index.php?action=preferences&action2=ajouter&idSpectacle=$id' class='button'>Ajouter à mes préférences</a>";
        $preferences = [];
        // Si l'utilisateur est connecté, récupérer ses préférences
        if (isset($_SESSION['user'])){
            $r = NRVRepository::getInstance();
            $user = unserialize($_SESSION['user']);
            $idUser = $user->id;
            $preferences = $r->getPreferencesFromUserId($idUser);
        }
        // Si l'utilisateur n'est pas connecté, récupérer ses préférences depuis les cookies
        elseif (isset($_COOKIE["preferences"])) {
            $preferences = unserialize($_COOKIE["preferences"]);
        }
        // Si l'ID du spectacle est dans le cookie, définir le bouton "Voir mes préférences"
        if (in_array($id, $preferences)) {
            $btnPref = "<a href='index.php?action=preferences' class='button'>Voir mes préférences</a>";
        }

        // Générer l'image du spectacle avec l'image de base
        $img = "<img alt='image du spectacle' src='{$image}'>";
        $containerId = "container" . $id;

        // Si le spectacle est annulé, superposer l'image avec l'image "ANNULÉ", et ajouter le label "ANNULÉ" au titre
        if($estAnnule){
            $img="<div id=".$containerId." class='image-container'></div>
                    <script src='src/superposerImages.js'></script>
                    <script>generateCompositeImage('".$image."' ,'img/annule.png','".$containerId."' );</script>";
            $titre = $titre ." [ANNULÉ]";
        }

        // on change le texte du bouton en fonction de l'état du spectacle
        $buttonText = $estAnnule ? "Rétablir" : "Annuler";

        // Si l'utilisateur est connecté et est un staff, définir les boutons "Annuler le spectacle" et "Modifier le spectacle"
        try {
            $user = AuthnProvider::getSignedInUser();
            $authz = new Authz($user);
            $authz->checkRole(User::$STAFF);
            $btnAnnuler = "<a href='index.php?action=AnnulerSpectacle&idSpectacle=$id' class='button'>$buttonText</a>  ";
            $btnModifSpec = "<a href='index.php?action=ModifSpectacle&idSpectacle=$id' class='button'>Modifier le spectacle</a>  ";
        } catch (\Exception $e) {
            // Aucun utilisateur connecté ou l'utilisateur n'est pas un admin
        }
        // Générer le code HTML
        return <<<FIN
                <br>
                 <div class="button-group">
                    $btnPref
                    $btnAnnuler
                    $btnModifSpec
                 </div>  
                <a href = "index.php?action=display-spectacle&id=$id"><div class='spectacle'>
                    <h2>{$titre}</h2>
                    $img
                    <p>Horaire de début : {$horaire}</p>
                    <p>Durée : {$heure}h$min</p>
                </div></a>
                FIN;
    }

    public function renderFull(): string{

        //Affichage détaillé d’un spectacle : titre, artistes, description, style, durée, image(s),extrait audio/vidéo,
        $estAnnule = filter_var($this->spectacle->estAnnule, FILTER_VALIDATE_BOOLEAN);
        $id = filter_var($this->spectacle->idSpectacle, FILTER_SANITIZE_SPECIAL_CHARS);
        $id = intval($id);
        $titre = filter_var($this->spectacle->titre, FILTER_SANITIZE_SPECIAL_CHARS);
        $vid = filter_var($this->spectacle->video, FILTER_SANITIZE_URL);
        $video = "<iframe width='560' height='315' src='{$vid}' title='YouTube video player' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' referrerpolicy='strict-origin-when-cross-origin' allowfullscreen></iframe>";

        $description = filter_var($this->spectacle->description, FILTER_SANITIZE_SPECIAL_CHARS);
        $duree = filter_var($this->spectacle->dureeSpec, FILTER_SANITIZE_SPECIAL_CHARS);
        $duree = intval($duree);
        $heure = floor($duree/60);
        $min = $duree%60;
        if($min<10)
            $min = "0".$min;
        $horaire = filter_var($this->spectacle->horaireSpec, FILTER_SANITIZE_SPECIAL_CHARS);

        // Générer les images du spectacle
        $images = '';
        foreach ($this->spectacle->images as $image) {
            $image = filter_var($image, FILTER_SANITIZE_URL);
            $img = "<img alt='image du spectacle' src='{$image}'>";
            $containerId = "container" . $id;
            // Si le spectacle est annulé, superposer l'image avec l'image "ANNULÉ", et ajouter le label "ANNULÉ" au titre
            if($estAnnule){
                $titre = $titre ." [ANNULÉ]";
            }
            $images .= $img;
        }
        // Générer les artistes du spectacle
        $artistes = '';
        foreach ($this->spectacle->artistes as $artiste) {
            $artiste = filter_var($artiste, FILTER_SANITIZE_SPECIAL_CHARS);
            $artistes .= "<p>{$artiste}</p>";
        }

        // Récupérer le style du spectacle
        $idStyle = $this->spectacle->idStyle;
        $style = NRVRepository::getInstance()->getStyleFromId($idStyle);

        // Définir le bouton "Ajouter à mes préférences"
        $btnPref = "<a href='index.php?action=preferences&action2=ajouter&idSpectacle=$id' class='button'>Ajouter à mes préférences</a>";
        $preferences = [];
        // Si l'utilisateur est connecté, récupérer ses préférences
        if (isset($_SESSION['user'])){
            $r = NRVRepository::getInstance();
            $user = unserialize($_SESSION['user']);
            $id = $user->id;
            $preferences = $r->getPreferencesFromUserId($id);
        }
        // Si l'utilisateur n'est pas connecté, récupérer ses préférences depuis les cookies
        elseif (isset($_COOKIE["preferences"])) {
            $preferences = unserialize($_COOKIE["preferences"]);
        }
        // Si l'ID du spectacle est dans le cookie, définir le bouton "Voir mes préférences"
        if (in_array($id, $preferences)) {
            $btnPref = "<a href='index.php?action=preferences' class='button'>Voir mes préférences</a>";
        }

        // Générer le code HTML
        return <<<FIN
                <div class='spectacle'>            
                    <h2>{$titre}</h2>
                    $btnPref
                    <h3>Artistes : </h3>                   
                    <p>{$artistes}</br></p>
                    <p>Description : {$description}</p>
                    <p>Style : {$style}</p>
                    <p>Durée : {$heure}h$min</p>
                    <p>Horaire de début : {$horaire}</p>
                    <p>Video : <br>{$video}</p>
                    <p>Images : <br>{$images}</p>                                        
                FIN;
    }

    /**
     * Méthode renderSoiree
     * Cette méthode permet de générer le code HTML pour afficher un spectacle dans le cadre d'une soirée
     * @return string Le code HTML généré
     */
    public function renderSoiree(){
        //Affichage pour une soiree : titre, artistes, description, style de musique, vidéo
        $artistes = '';
        // Générer les artistes du spectacle
        foreach ($this->spectacle->artistes as $artiste) {
            $artiste = filter_var($artiste, FILTER_SANITIZE_SPECIAL_CHARS);
            $artistes .= "<p>{$artiste}</p>";
        }
        // Générer la vidéo du spectacle
        $vid = filter_var($this->spectacle->video, FILTER_SANITIZE_URL);
        $video = "<iframe width='560' height='315' src='{$vid}' title='YouTube video player' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' referrerpolicy='strict-origin-when-cross-origin' allowfullscreen></iframe>";
        // Récupérer les informations du spectacle
        $titre = filter_var($this->spectacle->titre, FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($this->spectacle->description, FILTER_SANITIZE_SPECIAL_CHARS);
        $id = filter_var($this->spectacle->idSpectacle, FILTER_SANITIZE_SPECIAL_CHARS);
        $id = intval($id);

        // Récupérer le style du spectacle
        $idStyle = $this->spectacle->idStyle;
        $style = NRVRepository::getInstance()->getStyleFromId($idStyle);

        // Vérifier si le spectacle est annulé
        if($this->spectacle->estAnnule){
            $titre .= " [ANNULÉ]";
        }

        // Générer le code HTML
        return <<<FIN
                <a href = "index.php?action=display-spectacle&id=$id">
                <div class='spectacle'>
                    <h2>{$titre}</h2>
                    <h3>Artistes : </h3>
                    <p>{$artistes}</br></p>
                    <p>Description : {$description}</p>
                    <p>Style : {$style}</p>
                    <p>Vidéo : <br>{$video}</p>
                </div>
                </a>
                FIN;
    }

    /**
     * Méthode renderPreferences
     * Cette méthode permet de générer le code HTML pour afficher un spectacle dans le cadre des préférences de l'utilisateur
     * @return string Le code HTML généré
     */
    public function renderPreferences():string{
        // Affichage pour les préférences : titre, image

        // Récupérer les informations du spectacle
        $id=filter_var($this->spectacle->idSpectacle, FILTER_SANITIZE_NUMBER_INT);
        $image = filter_var($this->spectacle->images[0], FILTER_SANITIZE_URL);
        $titre = filter_var($this->spectacle->titre, FILTER_SANITIZE_SPECIAL_CHARS);
        $estAnnule = filter_var($this->spectacle->estAnnule, FILTER_SANITIZE_SPECIAL_CHARS);

        // Générer la première image du spectacle
        $img = "<img alt='image du spectacle' src='{$image}'>";
        $containerId = "container" . $id;
        // Si le spectacle est annulé, superposer une image d'annulation et ajouter le label "ANNULÉ" au titre
        if($estAnnule){
            $img="<div id=".$containerId." class='image-container'></div>
                    <script src='src/superposerImages.js'></script>
                    <script>generateCompositeImage('".$image."' ,'img/annule.png','".$containerId."' );</script>";
            $titre = $titre ." [ANNULÉ]";
        }

        // Générer le code HTML
        return <<<FIN
                <br>
                 <div class="button-group">
                    <a href='index.php?action=preferences&action2=supprimer&idSpectacle=$id' class='button'>Retirer de mes préférences</a>
                 </div>  
                <a href = "index.php?action=display-spectacle&id=$id"><div class='spectacle'>
                    <h2>{$titre}</h2>
                    <p>$img</p>
                </div></a>
                <br>
                FIN;
    }
}