<?php

namespace iutnc\nrv\festival;

class Soiree
{
    private int $idSoiree;
    private string $nomSoiree;
    private string $temathique;
    private string $dateSoiree;
    private string $horaireDebut;
    private $idLieu;

    public function __construct($nomSoiree, $temathique, $dateSoiree, $horaireDebut, $idLieu)
    {
        $this->idSoiree = 0;
        $this->nomSoiree = $nomSoiree;
        $this->temathique = $temathique;
        $this->dateSoiree = $dateSoiree;
        $this->horaireDebut = $horaireDebut;
        $this->idLieu = $idLieu;
    }

    public function __get(string $name)
    {
        return $this->$name;
    }

    public function setId(mixed $idSoiree)
    {
        $this->idSoiree = $idSoiree;
    }
}