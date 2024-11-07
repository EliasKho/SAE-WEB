<?php

namespace iutnc\nrv\dispatcher;

use iutnc\nrv\action as ACT;

class Dispatcher{
    protected string $action;
    public function __construct(){
        if (!isset($_GET['action'])){
            $this->action = 'default';
        }
        else {
            $this->action = $_GET['action'];
        }
    }

    public function run() : void
    {
        switch ($this->action) {
            case 'connexion':
                $act = new ACT\Connexion();
                break;
            case 'inscription':
                $act = new ACT\Inscription();
                break;
            case 'preferences':
                $act = new ACT\Preferences();
                break;
            case 'festival':
                $act = new ACT\Festival();
                break;
            default:
                $act = new ACT\DefaultAction();
                break;
        }
        $this->renderPage($act());
    }

    private function renderPage(string $html){
        $final = <<<FIN
        <!DOCTYPE html>
        <html lang='fr'>
        <meta charset='UTF-8'>
        <head>
            <title>NRV</title>         
            <link rel="stylesheet" href="styles.css"> <!-- Inclure le CSS -->
        </head>
        <body>
            <header>
                <h1>Bienvenue sur le site NRV</h1>
                <nav>
                    <ul>
                        <li><a href='index.php?action=connexion' class="button">Connexion</a></li>
                        <li><a href='index.php?action=inscription' class="button">Inscription</a></li>
                        <li><a href='index.php?action=preferences' class="button">Mes Préférences</a></li>
                        <li><a href='index.php?action=festival' class="button">Voir Festival</a></li>
                    </ul>
                </nav>
            </header>
            <div class="content">
                $html
            </div>
        </body>
        </html>
        FIN;
        echo $final;
    }

}