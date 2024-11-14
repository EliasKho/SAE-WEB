<?php

namespace iutnc\nrv\festival;

class Spectacle
{
    private int $idSpectacle;
    private string $titre;
    private string $description;
    private string $video;
    private string $horaireSpec;
    private string $dureeSpec;
    private int $idStyle;
    private array $images;
    private array $artistes;
    private bool $estAnnule;

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

    public function __get(string $name)
    {
        return $this->$name;
    }

    public function changerAnnulation()
    {
        $this->estAnnule = !$this->estAnnule;
    }


}