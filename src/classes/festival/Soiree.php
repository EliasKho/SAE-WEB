<?php

namespace iutnc\nrv\festival;

class Soiree
{
    private int $idSoiree;
    private string $nomSoiree;
    private string $temathique;
    private string $dateSoiree;
    private string $horaireDebut;
    private float $tarif;
    private $idLieu;

    public function __construct(string $nomSoiree,string $temathique,string $dateSoiree,string $horaireDebut,float $tarif,int $idLieu, int $id=0)
    {
        $this->idSoiree = $id;
        $this->nomSoiree = $nomSoiree;
        $this->temathique = $temathique;
        $this->dateSoiree = $dateSoiree;
        $this->horaireDebut = $horaireDebut;
        $this->tarif = $tarif;
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