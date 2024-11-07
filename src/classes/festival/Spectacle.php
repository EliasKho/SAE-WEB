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
    private String $style;

    public function __construct($titre, $description, $video, $horaireSpec, $dureeSpec, $style)
    {
        $this->idSpectacle = 0;
        $this->titre = $titre;
        $this->description = $description;
        $this->video = $video;
        $this->horaireSpec = $horaireSpec;
        $this->dureeSpec = $dureeSpec;
        $this->style = $style;
    }

    public function __get(string $name)
    {
        return $this->$name;
    }

    public function setId(int $id)
    {
        $this->idSpectacle = $id;
    }

}