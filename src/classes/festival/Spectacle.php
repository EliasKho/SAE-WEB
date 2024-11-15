<?php

namespace iutnc\nrv\festival;

/**
 * Classe Spectacle : représente un spectacle du festival
 */
class Spectacle{
    // Attributs de la classe Spectacle
    private int $idSpectacle;
    private string $titre;
    private string $description;
    private string $video;
    private string $horaireSpec;
    private string $dureeSpec;
    private int $idStyle;
    // Tableaux d'images et d'artistes
    private array $images;
    private array $artistes;
    // Attribut pour savoir si le spectacle est annulé
    private bool $estAnnule;

    /**
     * Constructeur de la classe Spectacle
     * @param string $titre : le titre du spectacle
     * @param string $description : la description du spectacle
     * @param string $video : le lien de la vidéo du spectacle
     * @param string $horaireSpec : l'horaire du spectacle
     * @param string $dureeSpec : la durée du spectacle
     * @param int $idStyle : l'identifiant du style du spectacle
     * @param bool $estAnnule : l'état d'annulation du spectacle (par défaut false)
     * @param int $idSpectacle : l'identifiant du spectacle (par défaut 0)
     * @param array $images : les images du spectacle (par défaut [])
     * @param array $artistes : les artistes du spectacle (par défaut [])
     */
    public function __construct($titre, $description, $video, $horaireSpec, $dureeSpec, $idStyle, $estAnnule=false, $idSpectacle=0, $images=[], $artistes=[])
    {
        $this->idSpectacle = $idSpectacle;
        $this->titre = $titre;
        $this->description = $description;
        $this->video = $video;
        $this->horaireSpec = $horaireSpec;
        $this->dureeSpec = $dureeSpec;
        $this->idStyle = $idStyle;
        $this->images = $images;
        $this->artistes = $artistes;
        $this->estAnnule = $estAnnule;
    }

    /**
     * Méthode magique __get pour récupérer les propriétés protégées
     * @param string $at Nom de la propriété à récupérer
     * @return mixed Valeur de la propriété
     * @throws \Exception Si la propriété est invalide
     */
    public function __get(string $at): mixed
    {
        if (property_exists($this, $at)) {
            return $this->$at;
        } else {
            throw new \Exception("$at: propriété invalide");
        }
    }

    /**
     * Modifie l'annulation du spectacle
     */
    public function changerAnnulation(){
        // Inversion de l'état d'annulation du spectacle (true -> false, false -> true)
        $this->estAnnule = !$this->estAnnule;
    }
}