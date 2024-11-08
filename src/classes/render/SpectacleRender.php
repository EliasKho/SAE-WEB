<?php

namespace iutnc\nrv\render;

use iutnc\nrv\festival\Spectacle;

class SpectacleRender
{
    private Spectacle $spectacle;

    public function __construct(Spectacle $spectacle)
    {
        $this->spectacle = $spectacle;
    }

    public function renderCompact(): string
    {
        $id=$this->spectacle->idSpectacle;
        $image = $this->spectacle->images[0];
        return <<<FIN
                <a href = "index.php?action=display-spectacle&id={$id}"><div class='spectacle'>
                    <h2>{$this->spectacle->titre}</h2>
                    <img src='{$image}'>
                    <p>{$this->spectacle->horaireSpec}</p>
                    <p>{$this->spectacle->dureeSpec}</p>
                </div></a>
                FIN;
    }

    public function renderFull(): string
    {
        //Affichage détaillé d’un spectacle : titre, artistes, description, style, durée, image(s),extrait audio/vidéo,

        $images = '';
        foreach ($this->spectacle->images as $image) {
            $images .= "<img src='{$image}'>";
        }
        $artistes = '';
        foreach ($this->spectacle->artistes as $artiste) {
            $artistes .= "<p>{$artiste}</p>";
        }
        $video = "<video controls><source src='{$this->spectacle->video}' type='video/mp4'></video>";
        return <<<FIN
                <div class='spectacle'>
                    <h2>{$this->spectacle->titre}</h2>
                    <h3>Artistes : </h3>
                    <p>{$artistes}</br></p>
                    <p>{$this->spectacle->description}</p>
                    <p>{$this->spectacle->style}</p>
                    <p>{$this->spectacle->dureeSpec}</p>
                    <p>{$this->spectacle->horaireSpec}</p>
                    <p>{$video}</p>
                    <p>{$images}</p>
                </div>
                FIN;
    }
}