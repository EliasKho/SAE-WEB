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
        return "<div class='spectacle'>
                    <h2>{$this->spectacle->titre}</h2>
                    <p>{$this->spectacle->description}</p>
                    <video controls>
                        <source src='{$this->spectacle->video}' type='video/mp4'>
                    </video>
                    <p>{$this->spectacle->horaireSpec}</p>
                    <p>{$this->spectacle->dureeSpec}</p>
                </div>";
    }
}