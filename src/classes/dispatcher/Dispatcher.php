<?php

namespace iutnc\nrv\dispatcher;

use iutnc\nrv\action as ACT;
use iutnc\nrv\auth\AuthnProvider;
use iutnc\nrv\auth\Authz;
use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\exception\AuthorizationException;
use iutnc\nrv\user\User;
use Exception;

/**
 * Class Dispatcher : classe permettant de dispatcher les actions et de générer la page HTML
 */
class Dispatcher {
    // action à effectuer
    protected string $action;

    /**
     * Constructeur de la classe Dispatcher, initialise l'action à effectuer
     */
    public function __construct(){
        // on récupère l'action à effectuer depuis la query string, ou on met 'default' par défaut
        $this->action = $_GET['action'] ?? 'default';
    }

    /**
     * Méthode run : exécute l'action demandée
     */
    public function run(): void{
        // on crée une instance de la classe correspondant à l'action demandée
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
            case 'add-soiree':
                $act = new ACT\AjouterSoiree();
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
                $act = new ACT\DisplayAllSoirees();
                break;
            case 'AnnulerSpectacle':
                $act = new ACT\AnnulerRetablirSpectacle();
                break;
            case 'ModifSpectacle':
                $act = new ACT\ModifierSpectacle();
                break;
            case 'ajouter-spec-soiree':
                $act = new ACT\AjouterSpectacleSoiree();
                break;
            case 'modifier-soiree':
                $act = new ACT\ModifierSpectacleSoiree();
                break;
            default:
                $act = new ACT\DefaultAction();
                break;
        }
        // on exécute l'action grâce à invoke et on affiche le résultat
        $this->renderPage($act());
    }

    /**
     * Méthode renderPage : génère la page HTML à partir du contenu HTML passé en paramètre
     * @param string $html : contenu HTML à afficher
     */
    private function renderPage(string $html): void{
        $staffMenu='';
        $adminMenu='';
        $connexion='';
        // on affiche le bouton de déconnexion de l'utilisateur
        $logOut="<li><a href='index.php?action=deconnexion' class='button'>Déconnexion</a></li>";
        try {
            // on vérifie si l'utilisateur est connecté
            $user = AuthnProvider::getSignedInUser();

            // on vérifie si l'utilisateur est admin ou staff
            $authz = new Authz($user);
            $authz->checkRole(User::$STAFF);
            // si l'utilisateur est staff, on affiche les boutons pour ajouter un spectacle et une soirée
            $staffMenu = "<li><a href='index.php?action=add-spectacle' class='button'>Ajouter Spectacle</a></li>";
            $staffMenu .= "<li><a href='index.php?action=add-soiree' class='button'>Ajouter Soirée</a></li>";

            $authz->checkRole(User::$ADMIN);
            // si l'utilisateur est admin, on affiche le bouton pour créer un staff
            $adminMenu = "<li><a href='index.php?action=creerStaff' class='button'>Créer Staff</a></li>";
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

        // on génère la page HTML finale, en ajoutant le contenu HTML passé en paramètre
        $final = <<<FIN
        <!DOCTYPE html>
        <html lang='fr'>
        <meta charset='UTF-8'>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <head>
            <title>NRV</title>
            <link rel="stylesheet" href="styles.css">
        </head>
        <body>
            <header>
                <h1 class="centre" id="titrer">Bienvenue sur le site NRV</h1>
                <nav>
                    <ul id="ulmenu">
                        <li><a href='index.php?action=menu' class='button'>Accueil</a></li>
                        $connexion
                        <li><a href='index.php?action=preferences' class="button">Mes Préférences</a></li>
                        <li><a href='index.php?action=spectacles' class="button">Spectacles</a></li>
                        <li><a href='index.php?action=soiree' class="button">Soirées</a></li>
                        $staffMenu
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
