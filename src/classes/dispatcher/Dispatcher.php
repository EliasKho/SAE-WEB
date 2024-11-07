<?php

namespace iutnc\nrv\dispatcher;

require_once 'vendor/autoload.php';
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
        </head>
        <body>
                <ul>
                    <li><a href='index.php?action=default'>Deefy</a></li>
                <div class="content">
                    $html
                </div>
        </body>
        </html>
        FIN;
        echo $final;
    }
}