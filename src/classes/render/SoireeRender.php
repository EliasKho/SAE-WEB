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
        $images="";
        foreach ($i as $image) {
            $image = filter_var($image, FILTER_SANITIZE_URL);
            $images .= "<img alt='image du lieu de la soiree' src='{$image}'>";
        }
        $tarif=$r->getTarifByIdSoiree($this->soiree->idSoiree);
        $spectacles = $r->getSpectaclesBySoiree($this->soiree->idSoiree);

        $addButton = "";
        $modifierButton = "";
        try {
            $user = AuthnProvider::getSignedInUser();
            $authz = new Authz($user);
            if ($authz->checkRole(User::$STAFF)) {
                $addButton = "<a href='index.php?action=ajouter-spec-soiree&idSoiree={$this->soiree->idSoiree}' class='button'>Ajouter un spectacle</a>  ";
                $modifierButton = "<a href='index.php?action=modifier-soiree&idSoiree={$this->soiree->idSoiree}' class='button'>Modifier la soirée</a>  ";
            }
        } catch (\Exception $e) {
            // Aucun utilisateur connecté ou l'utilisateur n'est pas un staff
        }
        $html= <<<FIN
                <br>
                <div class='soiree'>
                    <h2>{$nom}</h2>
                    <p>Thématique : {$thematique}</p>
                    <p>Date de la soirée : {$dateSoiree}</p>
                    <p>Horaire de début : {$horaireDebut}</p>
                    <p>Lieu : {$nomLieu}</p>
                    $images
                    <p>Tarif : {$tarif}€</p>
                    <h1>Spectacles de la soiree</h1>
                FIN;
        foreach ($spectacles as $spectacle) {
            $html.="<br><p>---------------------------------------------------------------</p>";
            $render = new SpectacleRender($spectacle);
            $html .= $render->renderSoiree();
            $html.="<p>---------------------------------------------------------------</p>";
        }
        $html .= <<<FIN
                <p>{$addButton}</p>
                </div>
                FIN;
        return $html;
    }
}