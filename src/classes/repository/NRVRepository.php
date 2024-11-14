<?php

namespace iutnc\nrv\repository;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\festival\Soiree;
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
            $spectacle = $this->getSpectacleFromId($id);
            $spectacles[] = $spectacle;
        }

        return $spectacles;
    }

    public function getSpectaclesByTri(string $date, string $style, string $lieu): array
    {
        if ($date == "" && $style == "" && $lieu == "") {
            return $this->getAllSpectacles();
        }

        $query = "SELECT * FROM spectacle 
                                            JOIN appartient on spectacle.idSpectacle = appartient.idSpectacle
                                            JOIN soiree on appartient.idSoiree = soiree.idSoiree
                                            JOIN lieu on soiree.idLieu = lieu.idLieu";
        $nbParams = 0;
        if ($date != "") {
            $query .= " WHERE soiree.dateSoiree = STR_TO_DATE(?, '%Y-%m-%d')";
            $nbParams++;
        }
        else {
            $query .= " WHERE 1=1";
        }

        if ($style!= "") {
            $query.= " AND spectacle.idStyle =?";
            $nbParams++;
        }

        if ($lieu!= "") {
            $query.= " AND lieu.idLieu =?";
            $nbParams++;
        }

        $requete = $this->pdo->prepare($query);

        if ($lieu != "" & $nbParams > 0) {
            $requete->bindParam($nbParams, $lieu);
            $nbParams--;
        }
        if ($style != "" & $nbParams > 0) {
            $requete->bindParam($nbParams, $style);
            $nbParams--;
        }
        if ($date != "" & $nbParams > 0) {
            $requete->bindParam($nbParams, $date);
        }


        $requete->execute();
        $spectacles = [];
        while ($s = $requete->fetch(\PDO::FETCH_ASSOC)) {
            $id = $s['idSpectacle'];
            $spectacle = $this->getSpectacleFromId($id);
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

    public function getUserFromMail(string $email) : User{
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $request = $this->pdo->prepare("SELECT * FROM users WHERE email =?");
        $request->bindParam(1, $email);
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

    public function getImagesByLieu(int $idLieu){
        $stmt = $this->pdo->prepare("SELECT * FROM image inner join imageLieu on imageLieu.idImage = image.idImage WHERE idLieu = ?");
        $stmt->bindParam(1, $idLieu);
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

    public function inscription(User $user, string $password, int $role):User{
        $username = $user->username;
        $email = $user->email;
        //Insertion des données
        $stm1 = $this->pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stm1->bindParam(':username', $username, PDO::PARAM_STR);
        $stm1->bindParam(':email', $email, PDO::PARAM_STR);
        $stm1->bindParam(':password', $password, PDO::PARAM_STR);
        $stm1->bindParam(':role', $role, PDO::PARAM_STR);
        $stm1->execute();

        $user->setId($this->pdo->lastInsertId());
        $user->setRole($role);
        return $user;
    }

    public function getAllStyles(){
        $stmt = $this->pdo->prepare("SELECT * FROM style");
        $stmt->execute();
        $styles = [];
        while ($s = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $styles[] = $s['nomStyle'];
        }
        return $styles;
    }

    public function getAllLieux(){
        $stmt = $this->pdo->prepare("SELECT * FROM lieu");
        $stmt->execute();
        $lieux = [];
        while ($l = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $lieux[] = $l['nomLieu'];
        }
        return $lieux;
    }

    public function getNomLieu(int $id){
        $stmt = $this->pdo->prepare("SELECT nomLieu FROM lieu WHERE idLieu = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $l = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $l['nomLieu'];
    }

    public function ajouterSpectacle (string $titre, string $horaire, int $duree, string $desc, int $style, array $images, string $video):Spectacle{
        $stmt = $this->pdo->prepare("INSERT INTO spectacle (titre, description, video, horaireSpec, dureeSpec, idStyle) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $titre);
        $stmt->bindParam(2, $desc);
        $stmt->bindParam(3, $video);
        $stmt->bindParam(4, $horaire);
        $stmt->bindParam(5, $duree);
        $stmt->bindParam(6, $style);
        $stmt->execute();
        $idSpec = $this->pdo->lastInsertId();
        $spectacle = new Spectacle($titre, $desc, $video, $horaire, $duree, $style, false, $idSpec);
        return $spectacle;
    }

    public function ajouterImage(string $chemin):int{
        $stmt = $this->pdo->prepare("INSERT INTO image (chemin) VALUES (?)");
        $stmt->bindParam(1, $chemin);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function lierSpectacleImage(int $idSpec, int $idImage){
        $stmt = $this->pdo->prepare("INSERT INTO imageSpec (idSpectacle, idImage) VALUES (?, ?)");
        $stmt->bindParam(1, $idSpec);
        $stmt->bindParam(2, $idImage);
        $stmt->execute();
    }

    public function getAllArtistes(){
        $stmt = $this->pdo->prepare("SELECT * FROM artiste");
        $stmt->execute();
        $artistes = $stmt->fetchAll();
        return $artistes;
    }

    public function lierSpectacleArtiste(int $idSpec, int $idArtiste){
        $stmt = $this->pdo->prepare("INSERT INTO jouer (idSpectacle, idArtiste) VALUES (?, ?)");
        $stmt->bindParam(1, $idSpec);
        $stmt->bindParam(2, $idArtiste);
        $stmt->execute();
    }

    public function updateEtatSpectacle(Spectacle $spectacle): void
    {
        $query = "UPDATE spectacle SET estAnnule = :estAnnule WHERE idSpectacle = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':estAnnule', $spectacle->estAnnule, \PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $spectacle->idSpectacle, \PDO::PARAM_INT);
        $stmt->execute();
    }

    public function ajouterSoiree(string $titre, string $thematique, string $date, string $horaire, float $tarif, int $lieu){
        $stmt = $this->pdo->prepare("INSERT INTO soiree (nomSoiree, thematique, dateSoiree, horaireDebut, tarif, idLieu) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $titre);
        $stmt->bindParam(2, $thematique);
        $stmt->bindParam(3, $date);
        $stmt->bindParam(4, $horaire);
        $stmt->bindParam(5, $tarif);
        $stmt->bindParam(6, $lieu);
        $stmt->execute();
    }

    public function getAllSoirees()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM soiree");
        $stmt->execute();
        while ($s = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $id = $s['idSoiree'];
            $soiree = $this->getSoireeById($id);
            $soirees[] = $soiree;
        }

        return $soirees;
    }

    public function getSpectacleFromId(int $idSpectacle): ?Spectacle
    {
        $query = "SELECT * FROM spectacle WHERE idSpectacle = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $idSpectacle]);

        $s = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($s) {
            $id = $s['idSpectacle'];
            $titre = $s['titre'];
            $description = $s['description'];
            $video = $s['video'];
            $horaire = $s['horaireSpec'];
            $duree = $s['dureeSpec'];
            $style = $s['idStyle'];
            $annule = (bool)$s['estAnnule'];

            $images = $this->getImagesBySpectacle($id);
            $artistes = $this->getArtistesBySpectacle($id);

            $spectacle = new Spectacle($titre, $description, $video, $horaire, $duree, $style, $annule, $id, $images, $artistes);

            return $spectacle;
        }

        return null;
    }

    public function updateSpectacle(int $id, string $titre, string $horaire, int $duree, string $description, int $style, array $images, string $video)
    {
        // Mise à jour des informations principales du spectacle
        $stmt = $this->pdo->prepare("UPDATE spectacle SET titre = ?, description = ?, video = ?, horaireSpec = ?, dureeSpec = ?, idStyle = ? WHERE idSpectacle = ?");
        $stmt->bindParam(1, $titre);
        $stmt->bindParam(2, $description);
        $stmt->bindParam(3, $video);
        $stmt->bindParam(4, $horaire);
        $stmt->bindParam(5, $duree);
        $stmt->bindParam(6, $style);
        $stmt->bindParam(7, $id);
        $stmt->execute();

        $nouvelleImage = array_filter($images['name'], fn($name) => !empty($name));

        if (!empty($nouvelleImage)) {
            // Suppression des anciennes associations avec les images et artistes (si nécessaire)
            $this->deleteSpectacleImages($id);

            // Ajout des nouvelles images
            foreach ($images['name'] as $index => $nom) {
                if (!empty($nom)) {
                    $tmpName = $images['tmp_name'][$index];
                    $extension = pathinfo($nom, PATHINFO_EXTENSION);
                    $nouveauNom = bin2hex(random_bytes(10)) . '.' . $extension;
                    move_uploaded_file($tmpName, "img/" . $nouveauNom);

                    // Ajout de l'image dans la base de données
                    $idImage = $this->ajouterImage($nouveauNom);
                    $this->lierSpectacleImage($id, $idImage);
                }
            }
        }
    }

    public function deleteSpectacleImages(int $idSpectacle)
    {
        $stmt = $this->pdo->prepare("DELETE FROM imageSpec WHERE idSpectacle = ?");
        $stmt->bindParam(1, $idSpectacle);
        $stmt->execute();
    }

    public function deleteSpectacleArtistes(int $idSpectacle)
    {
        $stmt = $this->pdo->prepare("DELETE FROM jouer WHERE idSpectacle = ?");
        $stmt->bindParam(1, $idSpectacle);
        $stmt->execute();
    }

    public function getInfosFromSpectacle($idSpectacle){
        $stmt = $this->pdo->prepare("SELECT * FROM soiree INNER JOIN appartient ON soiree.idSoiree = appartient.idSoiree WHERE idSpectacle = ?");
        $stmt->bindParam(1, $idSpectacle);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result;
    }

    public function getPreferences(int $idUser): array
    {
        $stmt = $this->pdo->prepare("SELECT idSpectacle FROM preference WHERE idUser = ?");
        $stmt->bindParam(1, $idUser);
        $stmt->execute();
        $preferences = [];
        while ($p = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $preferences[] = $p['idSpectacle'];
        }
        return $preferences;
    }

    public function supprimerPreference(int $idUser, int $idSpectacle)
    {
        $stmt = $this->pdo->prepare("DELETE FROM preference WHERE idUser = ? AND idSpectacle = ?");
        $stmt->bindParam(1, $idUser);
        $stmt->bindParam(2, $idSpectacle);
        $stmt->execute();
    }

    public function ajouterPreference(int $idUser, int $idSpectacle)
    {
        $stmt = $this->pdo->prepare("INSERT INTO preference (idUser, idSpectacle) VALUES (?, ?)");
        $stmt->bindParam(1, $idUser);
        $stmt->bindParam(2, $idSpectacle);
        $stmt->execute();
    }

    public function getSpectaclesBySoiree(int $idSoiree)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM appartient WHERE idSoiree = ?");
        $stmt->bindParam(1, $idSoiree);
        $stmt->execute();
        $spectacles = [];
        while ($s = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $spectacle = $this->getSpectacleFromId($s['idSpectacle']);
            $spectacles[] = $spectacle;
        }
        return $spectacles;
    }

    public function getSoireeBySpectacle(int $idSpectacle): int
    {
        $stmt = $this->pdo->prepare("SELECT * FROM appartient WHERE idSpectacle = ?");
        $stmt->bindParam(1, $idSpectacle);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['idSoiree'];
    }

    public function getSoireeById(int $id):Soiree{
        $stmt = $this->pdo->prepare("SELECT * FROM soiree WHERE idSoiree =?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $soiree = new Soiree($result['nomSoiree'], $result['thematique'], $result['dateSoiree'], $result['horaireDebut'], $result['tarif'], $result['idLieu'], $id);
        return $soiree;
    }

    public function ajouterSpectacleSoiree(int $idSoiree,int $idSpectacle){
        $stmt = $this->pdo->prepare("INSERT INTO appartient (idSoiree, idSpectacle) VALUES (?, ?)");
        $stmt->bindParam(1, $idSoiree);
        $stmt->bindParam(2, $idSpectacle);
        $stmt->execute();
    }

    public function getStyleById(int $idStyle) : string{
        $stmt = $this->pdo->prepare("SELECT nomStyle FROM style WHERE idStyle = ?");
        $stmt->bindParam(1, $idStyle);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['nomStyle'];
    }
}