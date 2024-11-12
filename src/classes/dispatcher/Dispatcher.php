<?php

namespace iutnc\nrv\dispatcher;

use iutnc\nrv\action as ACT;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\exception\AuthorizationException;
use iutnc\nrv\user\User;
use Exception;

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
            case 'spectacles':
                $act = new ACT\Spectacles();
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
            case 'deconnexion':
                $act = new ACT\Deconnexion();
                break;
            case 'soiree':
                $act = new ACT\Soiree();
                break;
            case 'AnnulerSpectacle':
                $act = new ACT\AnnulerRetablirSpectacle();
                break;
            default:
                $act = new ACT\DefaultAction();
                break;
        }
        $this->renderPage($act());
    }

    private function renderPage(string $html): void
    {
        $adminMenu='';
        $connexion='';
        $logOut="<li><a href='index.php?action=deconnexion' class='button'>Déconnexion</a></li>";
        try {
            $user = AuthnProvider::getSignedInUser();
            $authz = new Authz($user);
            if ($authz->checkRole(User::$ADMIN)) {
                $adminMenu = "<li><a href='index.php?action=creerStaff' class='button'>Créer Staff</a></li>";
            }
        } catch (AuthorizationException $e){
            //cas ou l'utilisateur n'est pas admin
            //on ne fait rien
        } catch (AuthnException $e) {
            //cas ou l'utilisateur n'est pas connecte
            //on retire le bouton de deconnexion et on lui propose de se connecter
            $logOut='';
            $connexion="<li><a href='index.php?action=connexion' class='button'>Connexion</a></li>
                 <li><a href='index.php?action=inscription' class='button'>Inscription</a></li>";
        }

        $final = <<<FIN
        <!DOCTYPE html>
        <html lang='fr'>
        <meta charset='UTF-8'>
        <head>
            <title>NRV</title>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body>
            <header>
                <h1>Bienvenue sur le site NRV</h1>
                <nav>
                    <ul id="ulmenu">
                        <li><a href='index.php?action=menu' class='button'>Accueil</a></li>
                        $connexion
                        <li><a href='index.php?action=preferences' class="button">Mes Préférences</a></li>
                        <li><a href='index.php?action=spectacles' class="button">Spectacles</a></li>
                        <li><a href='index.php?action=soiree' class="button">Soirées</a></li>
                        <li><a href='index.php?action=add-spectacle' class="button">Ajouter Spectacle</a></li>  <!-- Ajouter le lien pour ajouter un spectacle -->
                        $adminMenu
                        $logOut
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
