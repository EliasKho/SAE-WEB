<?php

namespace iutnc\nrv\action;

/**
 * Classe abstraite Action
 */
abstract class Action {

    // attributs
    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;

    /**
     * Constructeur de la classe Action qui initialise les attributs avec les valeurs des variables superglobales $_SERVER
     */
    public function __construct(){
        
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Méthode magique __invoke qui appelle la méthode execute, elle permet d'appeler un objet comme une fonction
     * @return string
     */
    public function __invoke() : string{
        return $this->execute();
    }

    /**
     * Méthode execute qui appelle la méthode executeGet ou executePost en fonction de la méthode HTTP utilisée
     * @return string
     */
    public function execute() : string{
        if ($this->http_method === "GET"){
            return $this->executeGet();
        }
        else {
            return $this->executePost();
        }
    }

    /**
     * Méthode executeGet qui doit être implémentée dans les classes filles
     * @return string
     */
    abstract protected function executeGet() : string;

    /**
     * Méthode executePost qui doit être implémentée dans les classes filles
     * @return string
     */
    abstract protected function executePost() : string;

}
