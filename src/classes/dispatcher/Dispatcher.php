<?php

namespace iutnc\nrv\dispatcher;

use iutnc\nrv\action as ACT;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\user\User;

class Dispatcher
{
    protected string $action;

    public function __construct()
    {
        $this->action = $_GET['action'] ?? 'default';
    }

    public function run(): void
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
            case 'display-spectacle':
                $act = new ACT\DisplaySpectacle();
                break;
            case 'add-spectacle':
                $act = new ACT\AjouterSpectacle();
                break;
            case 'creerStaff':
                $act = new ACT\CreerStaff();
                break;
            default:
                $act = new ACT\DefaultAction();
                break;
        }
        $this->renderPage($act());
    }

    private function renderPage(string $html): void
    {
        // Vérifier si un administrateur est connecté
        $adminMenu = '';
        try {
            $user = AuthnProvider::getSignedInUser();
            if ($user->getRole() === User::$ADMIN) {
                $adminMenu = "<li><a href='index.php?action=creerStaff' class='button'>Créer Staff</a></li>";
            }
        } catch (\Exception $e) {
            // Aucun utilisateur connecté ou l'utilisateur n'est pas un admin
        }

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
                    <ul id="ulmenu">
                        <li><a href='index.php?action=connexion' class="button">Connexion</a></li>
                        <li><a href='index.php?action=inscription' class="button">Inscription</a></li>
                        <li><a href='index.php?action=preferences' class="button">Mes Préférences</a></li>
                        <li><a href='index.php?action=festival' class="button">Voir Festival</a></li>
                        $adminMenu
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
