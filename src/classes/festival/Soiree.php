<?php

namespace iutnc\nrv\festival;

/**
 * Classe Soiree : représente une soirée du festival
 */
class Soiree {
    // Attributs
    private int $idSoiree;
    private string $nomSoiree;
    private string $temathique;
    private string $dateSoiree;
    private string $horaireDebut;
    private float $tarif;
    private $idLieu;

    /**
     * Constructeur de la classe Soiree
     * @param string $nomSoiree
     * @param string $temathique
     * @param string $dateSoiree
     * @param string $horaireDebut
     * @param float $tarif
     * @param int $idLieu
     * @param int $id : identifiant de la soirée (0 par défaut)
     */
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
}