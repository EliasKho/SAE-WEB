<?php
//le constructeur reçoit en paramètre
//◦ le prefixe des namespaces,
//◦ le chemin du répertoire de base correspondant au prefixe des namespaces ;
class Autoloader
{
    private $prefixe;
    private $repertoire;

    public function __construct($p, $r)
    {
        $this->prefixe = $p;
        $this->repertoire = $r;
    }

    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    public static function loadClass($class)
    {
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
//        $class = str_replace('lib' . DIRECTORY_SEPARATOR, '', $class);
        require_once $class . '.php';
    }
}