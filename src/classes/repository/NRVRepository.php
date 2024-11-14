<?php

namespace iutnc\nrv\repository;

use iutnc\nrv\exception\AuthnException;
use iutnc\nrv\festival\Soiree;
use iutnc\nrv\festival\Spectacle;
use iutnc\nrv\user\User;
use PDO;

/**
 * Class NRVRepository
 * classe qui gère toutes les requêtes à la base de données
 * @package iutnc\nrv\repository
 */
class NRVRepository{
    // attributs
    private \PDO $pdo;
    private static ?NRVRepository $instance = null;
    private static array $config = [];

    /**
     * Constructeur qui initialise la connexion à la base de données en fonction de l'attribut $conf
     * @param array $conf
     */
    private function __construct(array $conf)
    {
        $this->pdo = new \PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"]);
    }

    /**
     * Fonction qui retourne l'instance de la classe ou bien l'instancie si elle n'existe pas
     * Patron de conception Singleton
     * @return NRVRepository
     */
    public static function getInstance()
    {
        // si l'instance n'existe pas, on la crée, il faut avoir au préalable configuré la connexion
        if (is_null(self::$instance)) {
            self::$instance = new NRVRepository(self::$config);
        }
        return self::$instance;
    }

    /**
     * Fonction qui permet de configurer la connexion à la base de données
     * @param string $file
     * @throws \Exception
     */
    public static function setConfig(string $file){
        // on lit le fichier de configuration
        $conf = parse_ini_file($file);
        // si la lecture échoue, on lève une exception
        if ($conf === false) {
            throw new \Exception("Error reading configuration file");
        }
        // on stocke les informations de connexion
        $driver = $conf['driver'];
        $host = $conf['host'];
        $database = $conf['database'];
        $dsn = "$driver:host=$host;dbname=$database";
        // on stocke les informations de connexion dans l'attribut $config
        self::$config = ['dsn' => $dsn, 'user' => $conf['user'], 'pass' => $conf['pass']];
    }

    // fonctions getAll...()

    /**
     * Fonction qui retourne tous les spectacles
     * @return array
     */
    public function getAllSpectacles(): array
    {
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT * FROM spectacle inner join style on spectacle.idStyle = style.idStyle");
        $stmt->execute();
        // on récupère les résultats
        while ($s = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // on récupère l'id du spectacle
            $id = $s['idSpectacle'];
            // on crée un objet spectacle à partir de l'id
            $spectacle = $this->getSpectacleFromId($id);
            // on ajoute le spectacle à un tableau
            $spectacles[] = $spectacle;
        }
        return $spectacles;
    }

    /**
     * Fonction qui retourne tous les styles
     * @return array
     */
    public function getAllStyles() : array{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT * FROM style");
        $stmt->execute();
        $styles = [];
        // on récupère les résultats
        while ($s = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // on ajoute le nom du style à un tableau
            $styles[] = $s['nomStyle'];
        }
        return $styles;
    }

    /**
     * Fonction qui retourne tous les lieux
     * @return array
     */
    public function getAllLieux() : array{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT * FROM lieu");
        $stmt->execute();
        $lieux = [];
        // on récupère les résultats
        while ($l = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // on ajoute le nom du lieu à un tableau
            $lieux[] = $l['nomLieu'];
        }
        return $lieux;
    }

    /**
     * Fonction qui retourne tous les artistes
     * @return array
     */
    public function getAllArtistes() : array{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT * FROM artiste");
        $stmt->execute();
        // on récupère toutes les colonnes et on met dans un tableau
        $artistes = $stmt->fetchAll();
        return $artistes;
    }

    /**
     * Fonction qui retourne toutes les soirées
     * @return array
     */
    public function getAllSoirees() : array{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT * FROM soiree");
        $stmt->execute();
        // on récupère les résultats
        while ($s = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // on récupère l'id de la soirée
            $id = $s['idSoiree'];
            // on crée un objet soirée à partir de l'id
            $soiree = $this->getSoireeFromId($id);
            // on ajoute la soirée à un tableau
            $soirees[] = $soiree;
        }
        return $soirees;
    }


    // fonctions d'ajout

    /**
     * Fonction qui permet d'ajouter un utilisateur, elle retourne l'utilisateur créé avec son id
     * @param User $user
     * @param string $password
     * @param int $role
     * @return User
     */
    public function inscription(User $user, string $password, int $role):User{
        // on récupère les informations de l'utilisateur données
        $username = $user->username;
        $email = $user->email;

        //Insertion des données
        $stm1 = $this->pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stm1->bindParam(':username', $username, PDO::PARAM_STR);
        $stm1->bindParam(':email', $email, PDO::PARAM_STR);
        $stm1->bindParam(':password', $password, PDO::PARAM_STR);
        $stm1->bindParam(':role', $role, PDO::PARAM_STR);
        $stm1->execute();

        // on récupère l'id et le role de l'utilisateur
        $user->setId($this->pdo->lastInsertId());
        $user->setRole($role);
        return $user;
    }

    /**
     * Fonction qui permet d'ajouter un spectacle, elle retourne le spectacle créé
     * @param string $titre
     * @param string $horaire
     * @param int $duree
     * @param string $desc
     * @param int $style
     * @param string $video
     * @return Spectacle
     */
    public function ajouterSpectacle(string $titre, string $horaire, int $duree, string $desc, int $style, string $video):Spectacle{
        // on prépare une requête
        $stmt = $this->pdo->prepare("INSERT INTO spectacle (titre, description, video, horaireSpec, dureeSpec, idStyle) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $titre);
        $stmt->bindParam(2, $desc);
        $stmt->bindParam(3, $video);
        $stmt->bindParam(4, $horaire);
        $stmt->bindParam(5, $duree);
        $stmt->bindParam(6, $style);
        $stmt->execute();
        // on récupère l'id du spectacle
        $idSpec = $this->pdo->lastInsertId();
        // on crée un objet spectacle à partir des informations données
        $spectacle = new Spectacle($titre, $desc, $video, $horaire, $duree, $style, false, $idSpec);
        return $spectacle;
    }

    /**
     * Fonction qui permet d'ajouter une image, elle retourne l'id de l'image ajoutée
     * @param string $chemin chemin vers l'image
     * @return int
     */
    public function ajouterImage(string $chemin):int{
        // on prépare une requête
        $stmt = $this->pdo->prepare("INSERT INTO image (chemin) VALUES (?)");
        $stmt->bindParam(1, $chemin);
        $stmt->execute();
        // on récupère l'id de l'image et on le retourne
        return $this->pdo->lastInsertId();
    }

    /**
     * Fonction qui permet d'ajouter une soirée
     * @param string $titre
     * @param string $thematique
     * @param string $date
     * @param string $horaire
     * @param float $tarif
     * @param int $lieu
     */
    public function ajouterSoiree(string $titre, string $thematique, string $date, string $horaire, float $tarif, int $lieu){
        // on prépare une requête
        $stmt = $this->pdo->prepare("INSERT INTO soiree (nomSoiree, thematique, dateSoiree, horaireDebut, tarif, idLieu) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $titre);
        $stmt->bindParam(2, $thematique);
        $stmt->bindParam(3, $date);
        $stmt->bindParam(4, $horaire);
        $stmt->bindParam(5, $tarif);
        $stmt->bindParam(6, $lieu);
        // on exécute la requête
        $stmt->execute();
    }


    /**
     * Fonction qui permet d'ajouter un une préférence à un utilisateur
     * @param int $idUser
     * @param int $idSpectacle
     */
    public function ajouterPreference(int $idUser, int $idSpectacle){
        // on prépare une requête
        $stmt = $this->pdo->prepare("INSERT INTO preference (idUser, idSpectacle) VALUES (?, ?)");
        $stmt->bindParam(1, $idUser);
        $stmt->bindParam(2, $idSpectacle);
        // on exécute la requête
        $stmt->execute();
    }


    // fonctions de lien

    /**
     * Fonction qui permet de lier un spectacle à une soirée
     * @param int $idSoiree
     * @param int $idSpectacle
     */
    public function lierSpectacleSoiree(int $idSoiree, int $idSpectacle){
        // on prépare une requête
        $stmt = $this->pdo->prepare("INSERT INTO appartient (idSoiree, idSpectacle) VALUES (?, ?)");
        $stmt->bindParam(1, $idSoiree);
        $stmt->bindParam(2, $idSpectacle);
        // on exécute la requête
        $stmt->execute();
    }

    /**
     * Fonction qui permet de lier une image à un spectacle
     * @param int $idSpec
     * @param int $idImage
     */
    public function lierSpectacleImage(int $idSpec, int $idImage){
        // on prépare une requête
        $stmt = $this->pdo->prepare("INSERT INTO imageSpec (idSpectacle, idImage) VALUES (?, ?)");
        $stmt->bindParam(1, $idSpec);
        $stmt->bindParam(2, $idImage);
        // on exécute la requête
        $stmt->execute();
    }

    /**
     * Fonction qui permet de lier un artiste à un spectacle
     * @param int $idSpec
     * @param int $idArtiste
     */
    public function lierSpectacleArtiste(int $idSpec, int $idArtiste){
        // on prépare une requête
        $stmt = $this->pdo->prepare("INSERT INTO jouer (idSpectacle, idArtiste) VALUES (?, ?)");
        $stmt->bindParam(1, $idSpec);
        $stmt->bindParam(2, $idArtiste);
        // on exécute la requête
        $stmt->execute();
    }


    // fonctions de suppression


    /**
     * Fonction qui permet de supprimer les images d'un spectacle
     * @param int $idSpectacle
     */
    public function deleteSpectacleImages(int $idSpectacle){
        // on prépare une requête
        $stmt = $this->pdo->prepare("DELETE FROM imageSpec WHERE idSpectacle = ?");
        $stmt->bindParam(1, $idSpectacle);
        // on exécute la requête
        $stmt->execute();
    }


    /**
     * Fonction qui permet de supprimer les artistes d'un spectacle
     * @param int $idSpectacle
     */
    public function deleteSpectacleArtistes(int $idSpectacle){
        // on prépare une requête
        $stmt = $this->pdo->prepare("DELETE FROM jouer WHERE idSpectacle = ?");
        $stmt->bindParam(1, $idSpectacle);
        // on exécute la requête
        $stmt->execute();
    }

    /**
     * Fonction qui permet de supprimer une le préférence d'un utilisateur
     * @param int $idUser
     * @param int $idSpectacle
     */
    public function supprimerPreference(int $idUser, int $idSpectacle){
        // on prépare une requête
        $stmt = $this->pdo->prepare("DELETE FROM preference WHERE idUser = ? AND idSpectacle = ?");
        $stmt->bindParam(1, $idUser);
        $stmt->bindParam(2, $idSpectacle);
        // on exécute la requête
        $stmt->execute();
    }


    // fonctions de mise à jour


    /**
     * Fonction qui permet de mettre à jour les informations principales d'un spectacle
     * @param Spectacle $spectacle un objet contenant les informations
     */
    public function updateSpectacle(Spectacle $spectacle): void{
        // Mise à jour des informations principales du spectacle
        $stmt = $this->pdo->prepare("UPDATE spectacle SET titre = ?, description = ?, video = ?, horaireSpec = ?, dureeSpec = ?, idStyle = ?, estAnnule = ? WHERE idSpectacle = ?");
        $titre = $spectacle->titre;
        $description = $spectacle->description;
        $video = $spectacle->video;
        $horaire = $spectacle->horaireSpec;
        $duree = $spectacle->dureeSpec;
        $style = $spectacle->idStyle;
        $annule = (int)$spectacle->estAnnule;
        $id = $spectacle->idSpectacle;

        $stmt->execute([$titre, $description, $video, $horaire, $duree, $style, $annule, $id]);
    }

    /**
     * Fonction qui permet de mettre à jour les images d'un spectacle
     * @param int $idSpectacle
     * @param array $images
     * @throws \Exception
     */
    public function updateSpectacleImages(int $idSpectacle, array $images){
        // on ne fait rien si le tableau est vide
        if (!empty($images['name'][0])) {
            // on supprime toutes les images actuelles
            $this->deleteSpectacleImages($idSpectacle);
            // on ajoute les nouvelles images
            for ($i=0; $i < count($images['name']); $i++) {;
                if ($images['error'][$i] !== UPLOAD_ERR_OK) {
                    throw new \Exception("Erreur lors de l'upload du fichier.");
                }
                // on déplace le fichier en lui donnant un nom aléatoire
                $tmpName = $images['tmp_name'][$i];
                $nomFichier = bin2hex(random_bytes(10)) . '.' . pathinfo($images['name'][$i], PATHINFO_EXTENSION);
                $chemin = 'img/' . $nomFichier;
                if (!move_uploaded_file($tmpName, $chemin)) {
                    throw new \Exception("Erreur lors du déplacement du fichier.");
                }
                // on ajoute l'image à la base de données
                $idImage = $this->ajouterImage($nomFichier);
                // on lie l'image au spectacle
                $this->lierSpectacleImage($idSpectacle, $idImage);
            }
        }
    }

    /**
     * Fonction qui permet de mettre à jour les artistes d'un spectacle
     * @param int $idSpectacle
     * @param array $artistes
     */
    public function updateSpectacleArtistes($idSpectacle, array $artistes){
        // on ne fait rien si le tableau est vide
        if (!empty($artistes)) {
            // on supprime tous les artistes actuels
            $this->deleteSpectacleArtistes($idSpectacle);
            // on ajoute les nouveaux artistes
            foreach ($artistes as $artisteId) {
                $this->lierSpectacleArtiste($idSpectacle, $artisteId);
            }
        }
    }


    // fonctions get...FromId()

    /**
     * Fonction qui retourne un spectacle à partir de son id
     * @param int $idSpectacle
     * @return Spectacle
     */
    public function getSpectacleFromId(int $idSpectacle): ?Spectacle
    {
        // on prépare une requête
        $query = "SELECT * FROM spectacle WHERE idSpectacle = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $idSpectacle]);
        // on récupère le résultat
        $s = $stmt->fetch(\PDO::FETCH_ASSOC);
        // si on a un résultat, on crée un objet spectacle
        if ($s) {
            // on récupère les informations
            $id = $s['idSpectacle'];
            $titre = $s['titre'];
            $description = $s['description'];
            $video = $s['video'];
            $horaire = $s['horaireSpec'];
            $duree = $s['dureeSpec'];
            $style = $s['idStyle'];
            $annule = (bool)$s['estAnnule'];
            // utilisation de requêtes pour les images et les artistes
            $images = $this->getImagesFromSpectacleId($id);
            $artistes = $this->getArtistesFromSpectacleId($id);

            // on crée un objet spectacle qu'on retourne
            $spectacle = new Spectacle($titre, $description, $video, $horaire, $duree, $style, $annule, $id, $images, $artistes);
            return $spectacle;
        }
        return null;
    }

    /**
     * Fonction qui retourne une soirée à partir de son id
     * @param int $id
     * @return Soiree
     */
    public function getSoireeFromId(int $id):Soiree{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT * FROM soiree WHERE idSoiree =?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        // on récupère le résultat
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        // on crée un objet soirée qu'on retourne
        $soiree = new Soiree($result['nomSoiree'], $result['thematique'], $result['dateSoiree'], $result['horaireDebut'], $result['tarif'], $result['idLieu'], $id);
        return $soiree;
    }

    /**
     * Fonction qui retourne un style à partir de son id
     * @param int $idStyle
     * @return string
     */
    public function getStyleFromId(int $idStyle) : string{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT nomStyle FROM style WHERE idStyle = ?");
        $stmt->bindParam(1, $idStyle);
        $stmt->execute();
        // on récupère le résultat
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        // on retourne le nom du style
        return $result['nomStyle'];
    }

    /**
     * Fonction qui retourne le mot de passe hashé d'un utilisateur à partir de son id
     * @param int $id
     */
    public function getPasswordFromId(int $id) : string{
        // on prépare une requête qui retourne le mot de passe hashé
        $request = $this->pdo->prepare("SELECT password FROM users WHERE idUser = ?");
        $request->bindParam(1, $id);
        $request->execute();
        // on récupère le résultat
        $password = $request->fetch();
        // si on n'a pas de résultat, on lève une exception
        if ($password === false) {
            throw new AuthnException("Aucun mot de passe trouvé");
        }
        // on retourne le mot de passe
        return $password['password'];
    }

    /**
     * Fonction qui retourne le nom d'un lieu à partir de son id
     * @param int $id
     * @return string
     */
    public function getNomLieuFromId(int $id) : string{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT nomLieu FROM lieu WHERE idLieu = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        // on récupère le résultat
        $l = $stmt->fetch(\PDO::FETCH_ASSOC);
        // on retourne le nom du lieu
        return $l['nomLieu'];
    }


    // fonctions get...From... avec jointures


    /**
     * Fonction qui retourne les images d'un spectacle à partir de son id
     * @param int $id
     * @return array
     */
    public function getImagesFromSpectacleId(int $id) : array{
        // on prépare une requête qui retourne les images d'un spectacle
        $stmt = $this->pdo->prepare("SELECT * FROM image inner join imageSpec on imageSpec.idImage = image.idImage WHERE idSpectacle = ?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $images = [];
        // on récupère les résultats
        while ($i = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // on ajoute le chemin de l'image à un tableau
            $images[] = 'img/'.$i['chemin'];
        }
        // on retourne le tableau
        return $images;
    }

    /**
     * Fonction qui retourne les images d'un lieu à partir de son id
     * @param int $idLieu
     * @return array
     */
    public function getImagesFromLieuId(int $idLieu) : array{
        // on prépare une requête qui retourne les images d'un lieu
        $stmt = $this->pdo->prepare("SELECT * FROM image inner join imageLieu on imageLieu.idImage = image.idImage WHERE idLieu = ?");
        $stmt->bindParam(1, $idLieu);
        $stmt->execute();
        $images = [];
        // on récupère les résultats
        while ($i = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // on ajoute le chemin de l'image à un tableau
            $images[] = 'img/'.$i['chemin'];
        }
        // on retourne le tableau
        return $images;
    }

    /**
     * Fonction qui retourne les artistes d'un spectacle à partir de son id
     * @param int $id
     * @return array
     */
    public function getArtistesFromSpectacleId(int $id) : array{
        // on prépare une requête qui retourne les artistes d'un spectacle
        $stmt = $this->pdo->prepare("SELECT * FROM artiste inner join jouer on jouer.idArtiste = artiste.idArtiste WHERE idSpectacle =?");
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $artistes = [];
        // on récupère les résultats
        while ($a = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // on ajoute le nom de l'artiste à un tableau
            $artistes[] = $a['nomArtiste'];
        }
        // on retourne le tableau
        return $artistes;
    }

    /**
     * Fonction qui retourne des informations concernant un spectacle qui sont stockées dans la table soiree
     * @param int $idSpectacle
     */
    public function getInfosFromSpectacleId(int $idSpectacle): array{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT * FROM soiree INNER JOIN appartient ON soiree.idSoiree = appartient.idSoiree WHERE idSpectacle = ?");
        $stmt->bindParam(1, $idSpectacle);
        $stmt->execute();
        // on récupère le résultat et on le retourne
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Fonction qui retourne les préférences d'un utilisateur à partir de son id
     * @param int $idUser
     */
    public function getPreferencesFromUserId(int $idUser): array{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT idSpectacle FROM preference WHERE idUser = ?");
        $stmt->bindParam(1, $idUser);
        $stmt->execute();
        $preferences = [];
        // on récupère les résultats
        while ($p = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // on ajoute l'id du spectacle à un tableau
            $preferences[] = $p['idSpectacle'];
        }
        // on retourne le tableau
        return $preferences;
    }

    /**
     * Fonction qui retourne les spectacles d'une soirée à partir de son id
     * @param int $idSoiree
     */
    public function getSpectaclesFromSoireeId(int $idSoiree):array{
        // on prépare une requête
        $stmt = $this->pdo->prepare("SELECT * FROM appartient WHERE idSoiree = ?");
        $stmt->bindParam(1, $idSoiree);
        $stmt->execute();
        $spectacles = [];
        // on récupère les résultats
        while ($s = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            // on crée un objet spectacle à partir de l'id
            $spectacle = $this->getSpectacleFromId($s['idSpectacle']);
            // on ajoute le spectacle à un tableau
            $spectacles[] = $spectacle;
        }
        // on retourne le tableau
        return $spectacles;
    }

    /**
     * Fonction qui retourne l'id de la soirée à partir de l'id d'un spectacle
     * @param int $idSpectacle
     */
    public function getSoireeFromSpectacleId(int $idSpectacle): int{
        // on prépare une requête
        // un spectacle n'appartient qu'a une soirée en même temps
        $stmt = $this->pdo->prepare("SELECT * FROM appartient WHERE idSpectacle = ?");
        $stmt->bindParam(1, $idSpectacle);
        $stmt->execute();
        // on récupère le résultat
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        // on retourne l'id de la soirée
        return $result['idSoiree'];
    }


    // fonction recherche utilisateur

    /**
     * Fonction qui retourne un utilisateur à partir de son nom d'utilisateur si il existe
     * @param string $username
     * @return User
     */
    public function getUserFromUsername(string $username) : User{
        // on prépare une requête
        $request = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $request->bindParam(1, $username);
        $request->execute();
        // on récupère le résultat
        $user = $request->fetch();
        // si on n'a pas de résultat, on lève une exception
        if ($user === false) {
            throw new AuthnException("Aucun utilisateur trouvé");
        }
        // sinon on crée un objet utilisateur qu'on retourne
        $id = $user['idUser'];
        $role = $user['role'];

        $user = new User($user['username'], $user['email']);
        $user->setId($id);
        $user->setRole($role);
        return $user;
    }


    /**
     * Fonction qui retourne un utilisateur à partir de son email si il existe
     * @param string $email
     * @return User
     */
    public function getUserFromMail(string $email) : User{
        // on prépare une requête
        $request = $this->pdo->prepare("SELECT * FROM users WHERE email =?");
        $request->bindParam(1, $email);
        $request->execute();
        // on récupère le résultat
        $user = $request->fetch();
        // si on n'a pas de résultat, on lève une exception
        if ($user === false) {
            throw new AuthnException("Aucun utilisateur trouvé");
        }

        // sinon on crée un objet utilisateur qu'on retourne
        $id = $user['idUser'];
        $role = $user['role'];

        $user = new User($user['username'], $user['email']);
        $user->setId($id);
        $user->setRole($role);
        return $user;
    }

    // fonction de recherche avec tri

    /**
     * Fonction qui retourne les spectacles en fonction de la date, du style et du lieu
     * @param string $date
     * @param string $style
     * @param string $lieu
     * @return array
     */
    public function getSpectaclesByTri(string $date, string $style, string $lieu): array{
        // si les trois paramètres sont vides, on retourne tous les spectacles car aucun tri
        if ($date == "" && $style == "" && $lieu == "") {
            return $this->getAllSpectacles();
        }

        // on prépare le début de la requête : jointure sur toutes les tables utiles
        $query = "SELECT * FROM spectacle 
                                            JOIN appartient on spectacle.idSpectacle = appartient.idSpectacle
                                            JOIN soiree on appartient.idSoiree = soiree.idSoiree
                                            JOIN lieu on soiree.idLieu = lieu.idLieu";
        // on initialise le nombre de paramètres à 0
        $nbParams = 0;
        // on ajoute les conditions en fonction des paramètres donnés
        if ($date != "") {
            $query .= " WHERE soiree.dateSoiree = STR_TO_DATE(?, '%Y-%m-%d')";
            $nbParams++;
        }
        else {
            // condition présente pour éviter les erreurs de syntaxe
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
        // on prépare la requête
        $requete = $this->pdo->prepare($query);

        // on ajoute les paramètres dans le sens inverse en fonction du nombre de paramètres
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

        // on exécute la requête
        $requete->execute();
        $spectacles = [];
        // on récupère les résultats
        while ($s = $requete->fetch(\PDO::FETCH_ASSOC)) {
            // on récupère l'id du spectacle et on crée un objet spectacle
            $id = $s['idSpectacle'];
            $spectacle = $this->getSpectacleFromId($id);
            // on ajoute le spectacle à un tableau
            $spectacles[] = $spectacle;
        }
        // on retourne le tableau
        return $spectacles;
    }
}