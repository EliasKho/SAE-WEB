<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\soiree;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\repository\NRVRepository;
use iutnc\nrv\user\User;
use iutnc\nrv\exception\AuthnException;

/**
 * Classe permettant de générer le code HTML pour afficher une soirée de différentes manières
 */
class SoireeRender{
    // La soirée à afficher
    private Soiree $soiree;

    /**
     * Constructeur
     * @param Soiree $soiree La soirée à afficher
     */
    public function __construct(soiree $soiree)
    {
        $this->soiree = $soiree;
    }

    /**
     * Génère le code HTML pour afficher une soirée de manière complète
     * @return string Le code HTML
     */
    public function renderFull(): string
    {
        //Affichage détaillé d’une soiree : nom de la soirée, thématique, date et horaire, lieu [IMAGELIEU], tarifs, ainsi que la liste des spectacles : titre, artistes, description, style de musique, vidéo

        // On filtre les données pour éviter les attaques XSS
        $nom = filter_var($this->soiree->nomSoiree, FILTER_SANITIZE_SPECIAL_CHARS);
        $thematique = filter_var($this->soiree->temathique, FILTER_SANITIZE_SPECIAL_CHARS);
        $dateSoiree = filter_var($this->soiree->dateSoiree, FILTER_SANITIZE_SPECIAL_CHARS);
        $horaireDebut = filter_var($this->soiree->horaireDebut, FILTER_SANITIZE_SPECIAL_CHARS);
        $idLieu = filter_var($this->soiree->idLieu, FILTER_SANITIZE_SPECIAL_CHARS);

        // On récupère les informations du lieu (nom et images)
        $r = NRVRepository::getInstance();
        $nomLieu = $r->getNomLieuFromId($idLieu);
        $i = $r->getImagesFromLieuId($idLieu);
        $images="";
        // On filtre les données pour éviter les attaques XSS
        foreach ($i as $image) {
            $image = filter_var($image, FILTER_SANITIZE_URL);
            $images .= "<img alt='image du lieu de la soiree' src='{$image}'>";
        }

        // On filtre les données pour éviter les attaques XSS
        $tarif=filter_var($this->soiree->tarif, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        // On récupère les spectacles de la soirée
        $spectacles = $r->getSpectaclesFromSoireeId($this->soiree->idSoiree);

        $addButton = "";
        $modifierButton = "";
        try {
            // On vérifie si l'utilisateur est connecté et si c'est un staff
            $user = AuthnProvider::getSignedInUser();
            $authz = new Authz($user);
            $authz->checkRole(User::$STAFF);
            // Si c'est le cas, on affiche les boutons pour ajouter un spectacle et modifier la soirée
            $addButton = "<a href='index.php?action=ajouter-spec-soiree&idSoiree={$this->soiree->idSoiree}' class='button'>Ajouter un spectacle</a>  ";
            $modifierButton = "<a href='index.php?action=modifier-soiree&idSoiree={$this->soiree->idSoiree}' class='button'>Modifier la soirée</a>  ";
        } catch (\Exception $e) {
            // Aucun utilisateur connecté ou l'utilisateur n'est pas un staff
        }
        // on affiche les premières informations de la soirée
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
        // On affiche les spectacles de la soirée
        foreach ($spectacles as $spectacle) {
            $html.="<br><p>---------------------------------------------------------------</p>";
            $render = new SpectacleRender($spectacle);
            $html .= $render->renderSoiree();
            $html.="<p>---------------------------------------------------------------</p>";
        }
        // On affiche les boutons pour ajouter un spectacle
        $html .= <<<FIN
                <p>{$addButton}</p>
                <p>{$modifierButton}</p>
                </div>
                FIN;
        return $html;
    }
}