-- Table LIEU
CREATE TABLE LIEU (
                      idLieu INT PRIMARY KEY AUTO_INCREMENT,
                      nomLieu VARCHAR(50),
                      adresse VARCHAR(100),
                      nbPlacesAssises INT,
                      nbPlacesDebout INT
);

-- Table STYLE
CREATE TABLE STYLE (
                       idStyle INT PRIMARY KEY AUTO_INCREMENT,
                       nomStyle VARCHAR(50)
);

-- Table SOIREE
CREATE TABLE SOIREE (
                        idSoiree INT PRIMARY KEY AUTO_INCREMENT,
                        nomSoiree VARCHAR(50),
                        thematique VARCHAR(50),
                        dateSoiree DATE,
                        horaireDebut TIME,
                        idLieu INT,
                        FOREIGN KEY (idLieu) REFERENCES LIEU(idLieu) ON DELETE CASCADE
);

-- Table SPECTACLE
CREATE TABLE SPECTACLE (
                           idSpectacle INT PRIMARY KEY AUTO_INCREMENT,
                           titre VARCHAR(50),
                           description TEXT,
                           video VARCHAR(100),
                           horaireSpec TIME,
                           dureeSpec DECIMAL(3, 1),
                           idStyle INT,
                           FOREIGN KEY (idStyle) REFERENCES STYLE(idStyle) ON DELETE SET NULL
);

-- Table ARTISTE
CREATE TABLE ARTISTE (
                         idArtiste INT PRIMARY KEY AUTO_INCREMENT,
                         nomArtiste VARCHAR(50)
);

-- Table IMAGE
CREATE TABLE IMAGE (
                       idImage INT PRIMARY KEY AUTO_INCREMENT,
                       chemin VARCHAR(255)
);

-- Table JOUER
CREATE TABLE JOUER (
                       idSpectacle INT,
                       idArtiste INT,
                       FOREIGN KEY (idSpectacle) REFERENCES SPECTACLE(idSpectacle) ON DELETE CASCADE,
                       FOREIGN KEY (idArtiste) REFERENCES ARTISTE(idArtiste) ON DELETE CASCADE,
                       PRIMARY KEY (idSpectacle, idArtiste)
);

-- Table APPARTIENT
CREATE TABLE APPARTIENT (
                            idSoiree INT,
                            idSpectacle INT,
                            FOREIGN KEY (idSoiree) REFERENCES SOIREE(idSoiree) ON DELETE CASCADE,
                            FOREIGN KEY (idSpectacle) REFERENCES SPECTACLE(idSpectacle) ON DELETE CASCADE,
                            PRIMARY KEY (idSoiree, idSpectacle)
);

-- Table IMAGESPEC
CREATE TABLE IMAGESPEC (
                           idSpectacle INT,
                           idImage INT,
                           FOREIGN KEY (idSpectacle) REFERENCES SPECTACLE(idSpectacle) ON DELETE CASCADE,
                           FOREIGN KEY (idImage) REFERENCES IMAGE(idImage) ON DELETE CASCADE,
                           PRIMARY KEY (idSpectacle, idImage)
);

-- Table IMAGELIEU
CREATE TABLE IMAGELIEU (
                           idLieu INT,
                           idImage INT,
                           FOREIGN KEY (idLieu) REFERENCES LIEU(idLieu) ON DELETE CASCADE,
                           FOREIGN KEY (idImage) REFERENCES IMAGE(idImage) ON DELETE CASCADE,
                           PRIMARY KEY (idLieu, idImage)
);

-- Table USERS
CREATE TABLE USERS (
                       idUser INT PRIMARY KEY AUTO_INCREMENT,
                       username VARCHAR(50) NOT NULL UNIQUE,
                       email VARCHAR(100) NOT NULL UNIQUE,
                       password VARCHAR(100) NOT NULL,
                       role VARCHAR(10)
);

-- Table PREFERENCE
CREATE TABLE PREFERENCE (
                            idUser INT,
                            idSpectacle INT,
                            FOREIGN KEY (idUser) REFERENCES USERS(idUser) ON DELETE CASCADE,
                            FOREIGN KEY (idSpectacle) REFERENCES SPECTACLE(idSpectacle) ON DELETE CASCADE,
                            PRIMARY KEY (idUser, idSpectacle)
);







-- Insertion de données de test

-- Insertion de lieux
INSERT INTO LIEU (nomLieu, adresse, nbPlacesAssises, nbPlacesDebout) VALUES
                                                                         ('Grande Salle', '123 Rue de la Musique', 500, 200),
                                                                         ('Théâtre Municipal', '456 Avenue des Arts', 300, 100),
                                                                         ('Parc des Expositions', '789 Boulevard du Rock', 800, 500);

-- Insertion de styles de musique
INSERT INTO STYLE (nomStyle) VALUES
                                 ('Rock'),
                                 ('Blues'),
                                 ('Jazz'),
                                 ('Metal'),
                                 ('Pop');

-- Insertion de soirées
INSERT INTO SOIREE (nomSoiree, thematique, dateSoiree, horaireDebut, idLieu) VALUES
                                                                                 ('Soirée Rock Legends', 'Classic Rock', '2024-11-20', '19:00:00', 1),
                                                                                 ('Soirée Metal Madness', 'Metal', '2024-11-21', '20:00:00', 2),
                                                                                 ('Soirée Blues Vibes', 'Blues', '2024-11-22', '18:30:00', 1),
                                                                                 ('Soirée Jazz Night', 'Jazz', '2024-11-23', '19:00:00', 3),
                                                                                 ('Soirée Rock Revival', 'Rock', '2024-11-24', '20:30:00', 2),
                                                                                 ('Soirée Pop Party', 'Pop', '2024-11-25', '21:00:00', 3);

-- Insertion de spectacles
INSERT INTO SPECTACLE (titre, description, video, horaireSpec, dureeSpec, idStyle) VALUES
                                                                                       ('The Classic Rock Show', 'Un hommage aux légendes du rock', 'http://video.com/rockshow', '19:30:00', 1.5, 1),
                                                                                       ('Metal Overdrive', 'Concert de heavy metal intense', 'http://video.com/metaloverdrive', '20:15:00', 2.0, 4),
                                                                                       ('Blues Revival', 'Ambiance blues avec les meilleurs artistes', 'http://video.com/bluesrevival', '18:45:00', 1.8, 2),
                                                                                       ('Smooth Jazz Evening', 'Jazz classique et moderne', 'http://video.com/jazzevening', '19:15:00', 1.2, 3),
                                                                                       ('Rock Revival', 'Retour aux classiques du rock', 'http://video.com/rockrevival', '20:45:00', 1.7, 1),
                                                                                       ('Pop Extravaganza', 'Concert pop pour toute la famille', 'http://video.com/popextravaganza', '21:15:00', 1.3, 5);

-- Insertion d'artistes
INSERT INTO ARTISTE (nomArtiste) VALUES
                                     ('The Rockers'),
                                     ('Metal Fury'),
                                     ('Blues Brothers'),
                                     ('Jazz Masters'),
                                     ('Pop Stars');

-- Association des artistes aux spectacles (table JOUER)
INSERT INTO JOUER (idSpectacle, idArtiste) VALUES
                                               (1, 1),  -- The Rockers jouent dans "The Classic Rock Show"
                                               (2, 2),  -- Metal Fury joue dans "Metal Overdrive"
                                               (3, 3),  -- Blues Brothers jouent dans "Blues Revival"
                                               (4, 4),  -- Jazz Masters jouent dans "Smooth Jazz Evening"
                                               (5, 1),  -- The Rockers jouent aussi dans "Rock Revival"
                                               (6, 5);  -- Pop Stars jouent dans "Pop Extravaganza"

-- Association des spectacles aux soirées (table APPARTIENT)
INSERT INTO APPARTIENT (idSoiree, idSpectacle) VALUES
                                                   (1, 1),  -- "The Classic Rock Show" dans "Soirée Rock Legends"
                                                   (2, 2),  -- "Metal Overdrive" dans "Soirée Metal Madness"
                                                   (3, 3),  -- "Blues Revival" dans "Soirée Blues Vibes"
                                                   (4, 4),  -- "Smooth Jazz Evening" dans "Soirée Jazz Night"
                                                   (5, 5),  -- "Rock Revival" dans "Soirée Rock Revival"
                                                   (6, 6);  -- "Pop Extravaganza" dans "Soirée Pop Party"

-- Insertion d'images pour les spectacles et les lieux (table IMAGE)
INSERT INTO IMAGE (chemin) VALUES
                               ('rock_show.jpg'),
                               ('metal_overdrive.jpg'),
                               ('blues_revival.jpg'),
                               ('jazz_evening.jpg'),
                               ('rock_revival.jpg'),
                               ('pop_extravaganza.jpg'),
                               ('grande_salle.jpg'),
                               ('theatre_municipal.jpg'),
                               ('parc_expositions.jpg');

-- Association des images aux spectacles (table IMAGESPEC)
INSERT INTO IMAGESPEC (idSpectacle, idImage) VALUES
                                                 (1, 1),  -- Image pour "The Classic Rock Show"
                                                 (2, 2),  -- Image pour "Metal Overdrive"
                                                 (3, 3),  -- Image pour "Blues Revival"
                                                 (4, 4),  -- Image pour "Smooth Jazz Evening"
                                                 (5, 5),  -- Image pour "Rock Revival"
                                                 (6, 6);  -- Image pour "Pop Extravaganza"

-- Association des images aux lieux (table IMAGELIEU)
INSERT INTO IMAGELIEU (idLieu, idImage) VALUES
                                            (1, 7),  -- Image pour "Grande Salle"
                                            (2, 8),  -- Image pour "Théâtre Municipal"
                                            (3, 9);  -- Image pour "Parc des Expositions"

-- Insertion de comptes d'utilisateurs (table USERS)
INSERT INTO USERS (username, email, password, role) VALUES
                                                        ('admin_user', 'admin@example.com', 'hashed_password_admin', 'admin'),
                                                        ('staff_user', 'staff@example.com', 'hashed_password_staff', 'staff'),
                                                        ('standard_user', 'user@example.com', 'hashed_password_user', 'standard');

-- Insertion de préférences utilisateurs (table PREFERENCE)
INSERT INTO PREFERENCE (idUser, idSpectacle) VALUES
                                                 (3, 1),  -- standard_user a ajouté "The Classic Rock Show" à ses préférences
                                                 (3, 3),  -- standard_user a ajouté "Blues Revival" à ses préférences
                                                 (2, 2);  -- staff_user a ajouté "Metal Overdrive" à ses préférences
