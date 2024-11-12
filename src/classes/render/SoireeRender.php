<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\soiree;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;
use iutnc\nrv\exception\AuthnException;

class SoireeRender
{
    private Soiree $soiree;

    public function __construct(soiree $soiree)
    {
        $this->soiree = $soiree;
    }

//    Render spectacle repris
//    public function renderCompact(): string
//    {
//        $id=filter_var($this->soiree->idsoiree, FILTER_SANITIZE_NUMBER_INT);
//        $image = filter_var($this->soiree->images[0], FILTER_SANITIZE_URL);
//        $titre = filter_var($this->soiree->titre, FILTER_SANITIZE_SPECIAL_CHARS);
//        $horaire = filter_var($this->soiree->horaireSpec, FILTER_SANITIZE_SPECIAL_CHARS);
//        $duree = filter_var($this->soiree->dureeSpec, FILTER_SANITIZE_SPECIAL_CHARS);
//        $estAnnule = filter_var($this->soiree->estAnnule, FILTER_SANITIZE_SPECIAL_CHARS);
//        $btnAnnuler = "";
//
//        // Affichage du label "ANNULÉ" si le soiree est annulé
//        $annuleLabel = $estAnnule ? "<div class='annule'>ANNULÉ</div>" : "";
//
//        $buttonText = $estAnnule ? "Rétablir" : "Annuler";
//
//        try {
//            $user = AuthnProvider::getSignedInUser();
//            $authz = new Authz($user);
//            if ($authz->checkRole(User::$STAFF)) {
//                $btnAnnuler = "<a href='index.php?action=Annulersoiree&idsoiree=$id' class='button'>$buttonText</a>  ";
//            }
//        } catch (\Exception $e) {
//            // Aucun utilisateur connecté ou l'utilisateur n'est pas un admin
//        }
//
//        return <<<FIN
//                <br>
//                 <div class="button-group">
//                    <a href='index.php?action=preferences&idsoiree=$id' class='button'>Ajouter à mes préférences</a>
//                    $btnAnnuler
//                 </div>
//                <a href = "index.php?action=display-soiree&id=$id"><div class='soiree'>
//                    <p>{$annuleLabel}</p>
//                    <h2>{$titre}</h2>
//                    <img alt="image du soiree" src='{$image}'>
//                    <p>{$horaire}</p>
//                    <p>{$duree}</p>
//                </div></a>
//                FIN;
//    }

    public function renderFull(): string
    {
        //Affichage détaillé d’une soiree : nom de la soirée, thématique, date et horaire, lieu [IMAGELIEU], tarifs, ainsi que la liste des spectacles : titre, artistes, description, style de musique, vidéo

        $nom = filter_var($this->soiree->nomSoiree, FILTER_SANITIZE_SPECIAL_CHARS);
        $thematique = filter_var($this->soiree->temathique, FILTER_SANITIZE_SPECIAL_CHARS);
        $dateSoiree = filter_var($this->soiree->dateSoiree, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireDebut = filter_var($this->soiree->horaireDebut, FILTER_SANITIZE_SPECIAL_CHARS);
        $idLieu = filter_var($this->soiree->idLieu, FILTER_SANITIZE_SPECIAL_CHARS);
        $r = NRVRepository::getInstance();
        $nomLieu = $r->getNomLieu($idLieu);
        $i = $r->getImagesByLieu($idLieu);
        foreach ($i as $image) {
            $image = filter_var($image, FILTER_SANITIZE_URL);
            $images .= "<img alt='image de la soiree' src='{$image}'>";
        }
        $artistes = '';
        foreach ($this->soiree->artistes as $artiste) {
            $artiste = filter_var($artiste, FILTER_SANITIZE_SPECIAL_CHARS);
            $artistes .= "<p>{$artiste}</p>";
        }
        $vid = filter_var($this->soiree->video, FILTER_SANITIZE_URL);
        $video = "<iframe width='560' height='315' src='{$vid}' title='YouTube video player' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' referrerpolicy='strict-origin-when-cross-origin' allowfullscreen></iframe>";
        $titre = filter_var($this->soiree->titre, FILTER_SANITIZE_SPECIAL_CHARS);
        $description = filter_var($this->soiree->description, FILTER_SANITIZE_SPECIAL_CHARS);
        $style = filter_var($this->soiree->style, FILTER_SANITIZE_SPECIAL_CHARS);
        $duree = filter_var($this->soiree->dureeSpec, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaire = filter_var($this->soiree->horaireSpec, FILTER_SANITIZE_SPECIAL_CHARS);
        $estAnnule = filter_var($this->soiree->estAnnule, FILTER_VALIDATE_BOOLEAN);

        // Affichage du label "ANNULÉ" si le soiree est annulé
        $annuleLabel = $estAnnule ? "<div class='annule'>ANNULÉ</div>" : "";

        return <<<FIN
                <div class='soiree'>
                    {$annuleLabel}
                    <h2>{$titre}</h2>
                    <h3>Artistes : </h3>
                    <p>{$artistes}</br></p>
                    <p>{$description}</p>
                    <p>{$style}</p>
                    <p>{$duree}</p>
                    <p>{$horaire}</p>
                    <p>{$video}</p>
                    <p>{$images}</p>
                </div>
                FIN;
    }
}