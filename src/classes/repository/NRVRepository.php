<?php

namespace iutnc\nrv\repository;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\user\User;
use PDO;

class NRVRepository{
    private \PDO $pdo;
    private static ?NRVRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $this->pdo = new \PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"]);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new NRVRepository(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig(string $file)
    {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new \Exception("Error reading configuration file");
        }
        $driver = $conf['driver'];
        $host = $conf['host'];
        $database = $conf['database'];
        $dsn = "$driver:host=$host;dbname=$database";
        self::$config = ['dsn' => $dsn, 'user' => $conf['user'], 'pass' => $conf['pass']];
    }

    public function getAllSpectacles(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM spectacle inner join style on spectacle.idStyle = style.idStyle");
        $stmt->execute();
        while ($s = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $id = $s['idSpectacle'];
            $titre = $s['titre'];
            $description = $s['description'];
            $video = $s['video'];
            $horaire = $s['horaireSpec'];
            $duree = $s['dureeSpec'];
            $style = $s['nomStyle'];

            $images = $this->getImagesBySpectacle($id);
            $artistes = $this->getArtistesBySpectacle($id);

            $spectacle = new Spectacle($titre, $description, $video, $horaire, $duree, $style);
            $spectacle->setId($id);
            $spectacle->setImages($images);
            $spectacle->setArtistes($artistes);
            $spectacles[] = $spectacle;
        }

        return $spectacles;
    }

    public function getUserFromUsername(string $username) : User{
        $username = filter_var($username, FILTER_SANITIZE_SPECIAL_CHARS);
        $request = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $request->bindParam(1, $username);
        $request->execute();
        $user = $request->fetch();
        if ($user === false) {
            throw new AuthnException("Aucun utilisateur trouvé");
        }

        $id = $user['idUser'];
        $role = $user['role'];

        $user = new User($user['username'], $user['email']);
        $user->setId($id);
        $user->setRole($role);
        return $user;
    }

    public function getPasswordFromUser(User $user) : string{
        $request = $this->pdo->prepare("SELECT password FROM users WHERE idUser = ?");
        $id = $user->id;
        $request->bindParam(1, $id);
        $request->execute();
        $password = $request->fetch();
        if ($password === false) {
            throw new AuthnException("Aucun mot de passe trouvé");
        }
        return $password['password'];
    }

    public function getSpectacleById(int $idSpec){
        $stmt = $this->pdo->prepare("SELECT * FROM spectacle inner join style on spectacle.idStyle = style.idStyle WHERE idSpectacle = ?");
        $stmt->bindParam(1, $idSpec);
        $stmt->execute();
        $s = $stmt->fetch(\PDO::FETCH_ASSOC);
        $id = $s['idSpectacle'];
        $titre = $s['titre'];
        $description = $s['description'];
        $video = $s['video'];
        $horaire = $s['horaireSpec'];
        $duree = $s['dureeSpec'];
        $style = $s['nomStyle'];
        $images = $this->getImagesBySpectacle($id);
        $artistes = $this->getArtistesBySpectacle($id);

        $spectacle = new Spectacle($titre, $description, $video, $horaire, $duree, $style);
        $spectacle->setId($id);
        $spectacle->setImages($images);
        $spectacle->setArtistes($artistes);
        return $spectacle;
    }


    public function getImagesBySpectacle(int $id){
        $stmt = $this->pdo->prepare("SELECT * FROM image inner join imageSpec on imageSpec.idImage = image.idImage WHERE idSpectacle = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $images = [];
        while ($i = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $images[] = 'img/'.$i['chemin'];
        }
        return $images;
    }

    public function getArtistesBySpectacle(int $id){
        $stmt = $this->pdo->prepare("SELECT * FROM artiste inner join jouer on jouer.idArtiste = artiste.idArtiste WHERE idSpectacle =?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $artistes = [];
        while ($a = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $artistes[] = $a['nomArtiste'];
        }
        return $artistes;
    }

    public function inscription($username, $email, $password, $role): String{
        $stm = $this->pdo->prepare("SELECT*FROM users");
        $stm->execute();
        $s ="";
        // vérification si l'utilisateur ou l'email existe déjà
        while ($s = $stm->fetch(\PDO::FETCH_ASSOC)) {
            if ($s['email'] === $email) {

                return "Un utilisateur avec cet email existe déjà";
            }
            if ($s['username'] === $username) {
                return "Un utilisateur avec ce nom d'utilisateur existe déjà";
            }
        }

        $hashPassword = password_hash($password, PASSWORD_DEFAULT);

        //Insertion des données
        $stm1 = $this->pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stm1->bindParam(':username', $username, PDO::PARAM_STR);
        $stm1->bindParam(':email', $email, PDO::PARAM_STR);
        $stm1->bindParam(':password', $hashPassword, PDO::PARAM_STR);
        $stm1->bindParam(':role', $role, PDO::PARAM_STR);
        $stm1->execute();

        return "Inscription réussie";
    }
}