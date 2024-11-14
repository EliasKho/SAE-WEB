<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;
use iutnc\nrv\exception\AuthnException;

class SpectacleRender
{
    private Spectacle $spectacle;

    public function __construct(Spectacle $spectacle)
    {
        $this->spectacle = $spectacle;
    }

    public function renderCompact(): string
    {
        //Affichage compact d’un spectacle : titre, image, horaire, durée
        $id=filter_var($this->spectacle->idSpectacle, FILTER_SANITIZE_NUMBER_INT);
        $image = filter_var($this->spectacle->images[0], FILTER_SANITIZE_URL);
        $titre = filter_var($this->spectacle->titre, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaire = filter_var($this->spectacle->horaireSpec, FILTER_SANITIZE_SPECIAL_CHARS);
        $duree = filter_var($this->spectacle->dureeSpec, FILTER_SANITIZE_SPECIAL_CHARS);
        $estAnnule = filter_var($this->spectacle->estAnnule, FILTER_SANITIZE_SPECIAL_CHARS);
        $btnAnnuler = "";
        $btnModifSpec = "";
        $btnPref = "<a href='index.php?action=preferences&action2=ajouter&idSpectacle=$id' class='button'>Ajouter à mes préférences</a>";

        if (isset($_COOKIE["preferences"])) {
            $preferences = unserialize($_COOKIE["preferences"]);
            // Si l'ID du spectacle est dans le cookie, définir le bouton "Voir mes préférences"
            if (in_array($id, $preferences)) {
                $btnPref = "<a href='index.php?action=preferences' class='button'>Voir mes préférences</a>";
            }
        }

        $img = "<img alt='image du spectacle' src='{$image}'>";
        $containerId = "container" . $id;
        if($estAnnule){
            $img="<div id=".$containerId." class='image-container'></div>
                    <script src='src/superposerImages.js'></script>
                    <script>generateCompositeImage('".$image."' ,'img/annule.png','".$containerId."' );</script>";
            $titre = $titre ." [ANNULÉ]";
        }

        $buttonText = $estAnnule ? "Rétablir" : "Annuler";

        try {
            $user = AuthnProvider::getSignedInUser();
            $authz = new Authz($user);
            if ($authz->checkRole(User::$STAFF)) {
                $btnAnnuler = "<a href='index.php?action=AnnulerSpectacle&idSpectacle=$id' class='button'>$buttonText</a>  ";
                $btnModifSpec = "<a href='index.php?action=ModifSpectacle&idSpectacle=$id' class='button'>Modifier le spectacle</a>  ";
            }
        } catch (\Exception $e) {
            // Aucun utilisateur connecté ou l'utilisateur n'est pas un admin
        }

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
                    <p>{$horaire}</p>
                    <p>{$duree}</p>
                </div></a>
                FIN;
    }

    public function renderFull(): string
    {
        //Affichage détaillé d’un spectacle : titre, artistes, description, style, durée, image(s),extrait audio/vidéo,

        $images = '';
        foreach ($this->spectacle->images as $image) {
            $image = filter_var($image, FILTER_SANITIZE_URL);
            $images .= "<img alt='image du spectacle' src='{$image}'>";
        }
        $artistes = '';
        foreach ($this->spectacle->artistes as $artiste) {
            $artiste = filter_var($artiste, FILTER_SANITIZE_SPECIAL_CHARS);
            $artistes .= "<p>{$artiste}</p>";
        }
        $vid = filter_var($this->spectacle->video, FILTER_SANITIZE_URL);
        $video = "<iframe width='560' height='315' src='{$vid}' title='YouTube video player' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' referrerpolicy='strict-origin-when-cross-origin' allowfullscreen></iframe>";
        $titre = filter_var($this->spectacle->titre, FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($this->spectacle->description, FILTER_SANITIZE_SPECIAL_CHARS);
        $duree = filter_var($this->spectacle->dureeSpec, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaire = filter_var($this->spectacle->horaireSpec, FILTER_SANITIZE_SPECIAL_CHARS);
        $estAnnule = filter_var($this->spectacle->estAnnule, FILTER_VALIDATE_BOOLEAN);
        $id = filter_var($this->spectacle->idSpectacle, FILTER_SANITIZE_SPECIAL_CHARS);
        $id = intval($id);

        $idStyle = $this->spectacle->idStyle;
        $style = NRVRepository::getInstance()->getStyleById($idStyle);

        // Affichage du label "ANNULÉ" si le spectacle est annulé
        $annuleLabel = $estAnnule ? "<div class='annule'>ANNULÉ</div>" : "";

        $btnPref = "<a href='index.php?action=preferences&action2=ajouter&idSpectacle=$id' class='button'>Ajouter à mes préférences</a>";
        $preferences = [];
        if (isset($_SESSION['user'])){
            $r = NRVRepository::getInstance();
            $user = unserialize($_SESSION['user']);
            $id = $user->id;
            $preferences = $r->getPreferences($id);
        }
        elseif (isset($_COOKIE["preferences"])) {
            $preferences = unserialize($_COOKIE["preferences"]);
            // Si l'ID du spectacle est dans le cookie, définir le bouton "Voir mes préférences"
        }
        if (in_array($id, $preferences)) {
            $btnPref = "<a href='index.php?action=preferences' class='button'>Voir mes préférences</a>";
        }
        return <<<FIN
                <div class='spectacle'>
                    {$annuleLabel}                
                    <h2>{$titre}</h2>
                    $btnPref
                    <h3>Artistes : </h3>                   
                    <p>{$artistes}</br></p>
                    <p>{$description}</p>
                    <p>{$style}</p>
                    <p>{$duree}</p>
                    <p>{$horaire}</p>
                    <p>{$video}</p>
                    <p>{$images}</p>                                        
                FIN;
    }

    public function renderSoiree()
    {
        //Affichage pour une soiree : titre, artistes, description, style de musique, vidéo
        $artistes = '';
        foreach ($this->spectacle->artistes as $artiste) {
            $artiste = filter_var($artiste, FILTER_SANITIZE_SPECIAL_CHARS);
            $artistes .= "<p>{$artiste}</p>";
        }
        $vid = filter_var($this->spectacle->video, FILTER_SANITIZE_URL);
        $video = "<iframe width='560' height='315' src='{$vid}' title='YouTube video player' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' referrerpolicy='strict-origin-when-cross-origin' allowfullscreen></iframe>";
        $titre = filter_var($this->spectacle->titre, FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($this->spectacle->description, FILTER_SANITIZE_SPECIAL_CHARS);
        $id = filter_var($this->spectacle->idSpectacle, FILTER_SANITIZE_SPECIAL_CHARS);
        $id = intval($id);

        $idStyle = $this->spectacle->idStyle;
        $style = NRVRepository::getInstance()->getStyleById($idStyle);

        return <<<FIN
                <a href = "index.php?action=display-spectacle&id=$id">
                <div class='spectacle'>
                    <h2>{$titre}</h2>
                    <h3>Artistes : </h3>
                    <p>{$artistes}</br></p>
                    <p>{$description}</p>
                    <p>{$style}</p>
                    <p>{$video}</p>
                </div>
                </a>
                FIN;
    }

    public function renderPreferences():string
    {
        $id=filter_var($this->spectacle->idSpectacle, FILTER_SANITIZE_NUMBER_INT);
        $image = filter_var($this->spectacle->images[0], FILTER_SANITIZE_URL);
        $titre = filter_var($this->spectacle->titre, FILTER_SANITIZE_SPECIAL_CHARS);
        $estAnnule = filter_var($this->spectacle->estAnnule, FILTER_SANITIZE_SPECIAL_CHARS);

        // Affichage du label "ANNULÉ" si le spectacle est annulé
        $annuleLabel = $estAnnule ? "<div class='annule'>ANNULÉ</div>" : "";
        return <<<FIN
                <br>
                 <div class="button-group">
                    <a href='index.php?action=preferences&action2=supprimer&idSpectacle=$id' class='button'>Retirer de mes préférences</a>
                 </div>  
                <a href = "index.php?action=display-spectacle&id=$id"><div class='spectacle'>
                    <p>{$annuleLabel}</p>
                    <h2>{$titre}</h2>
                    <img alt="image du spectacle" src='{$image}'>
                </div></a>
                <br>
                FIN;
    }
}