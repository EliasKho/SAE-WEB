SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS IMAGESPEC, IMAGELIEU, JOUER, APPARTIENT, PREFERENCE,
    USERS, IMAGE, ARTISTE, SPECTACLE, SOIREE, LIEU, STYLE;

SET FOREIGN_KEY_CHECKS = 1;

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
                        tarif DECIMAL(5, 2),
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
                           dureeSpec INT,  -- Durée en minutes
                           idStyle INT,
                           estAnnule BOOLEAN DEFAULT FALSE,
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

-- Table JOUER (relation entre SPECTACLE et ARTISTE)
CREATE TABLE JOUER (
                       idSpectacle INT,
                       idArtiste INT,
                       FOREIGN KEY (idSpectacle) REFERENCES SPECTACLE(idSpectacle) ON DELETE CASCADE,
                       FOREIGN KEY (idArtiste) REFERENCES ARTISTE(idArtiste) ON DELETE CASCADE,
                       PRIMARY KEY (idSpectacle, idArtiste)
);

-- Table APPARTIENT (relation entre SOIREE et SPECTACLE)
CREATE TABLE APPARTIENT (
                            idSoiree INT,
                            idSpectacle INT,
                            FOREIGN KEY (idSoiree) REFERENCES SOIREE(idSoiree) ON DELETE CASCADE,
                            FOREIGN KEY (idSpectacle) REFERENCES SPECTACLE(idSpectacle) ON DELETE CASCADE,
                            PRIMARY KEY (idSoiree, idSpectacle)
);

-- Table IMAGESPEC (relation entre SPECTACLE et IMAGE)
CREATE TABLE IMAGESPEC (
                           idSpectacle INT,
                           idImage INT,
                           FOREIGN KEY (idSpectacle) REFERENCES SPECTACLE(idSpectacle) ON DELETE CASCADE,
                           FOREIGN KEY (idImage) REFERENCES IMAGE(idImage) ON DELETE CASCADE,
                           PRIMARY KEY (idSpectacle, idImage)
);

-- Table IMAGELIEU (relation entre LIEU et IMAGE)
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
                       role int(3) NOT NULL
);

-- Table PREFERENCE (relation entre USERS et SPECTACLE)
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
INSERT INTO SOIREE (nomSoiree, thematique, dateSoiree, horaireDebut, tarif, idLieu) VALUES
                                                                                 ('Soirée Rock Legends', 'Classic Rock', '2024-11-20', '19:00:00', 20, 1),
                                                                                 ('Soirée Metal Madness', 'Metal', '2024-11-21', '20:00:00', 22.50, 2),
                                                                                 ('Soirée Blues Vibes', 'Blues', '2024-11-22', '18:30:00', 18.25, 1),
                                                                                 ('Soirée Jazz Night', 'Jazz', '2024-11-23', '19:00:00', 17, 3),
                                                                                 ('Soirée Rock Revival', 'Rock', '2024-11-24', '20:30:00', 21, 2),
                                                                                 ('Soirée Pop Party', 'Pop', '2024-11-25', '21:00:00', 19.75, 3);

-- Insertion de spectacles avec la durée en minutes
INSERT INTO SPECTACLE (titre, description, video, horaireSpec, dureeSpec, idStyle) VALUES
                                                                                       ('The Classic Rock Show', 'Un hommage aux légendes du rock', 'https://www.youtube.com/embed/OBR0XtooX7w', '19:30:00', 90, 1),
                                                                                       ('Metal Overdrive', 'Concert de heavy metal intense', 'https://www.youtube.com/embed/VFOrGkAvjAE', '20:15:00', 120, 4),
                                                                                       ('Blues Revival', 'Ambiance blues avec les meilleurs artistes', 'https://www.youtube.com/embed/FUWxjHXyrlI', '18:45:00', 110, 2),
                                                                                       ('Smooth Jazz Evening', 'Jazz classique et moderne', 'https://www.youtube.com/embed/Bpe8Ch-zwWQ', '19:15:00', 75, 3),
                                                                                       ('Rock Revival', 'Retour aux classiques du rock', 'https://www.youtube.com/embed/-MAJx3hb4KY', '20:45:00', 105, 1),
                                                                                       ('Pop Extravaganza', 'Concert pop pour toute la famille', 'https://www.youtube.com/embed/Ed8Xx4Pv84w', '21:15:00', 80, 5);

-- Insertion d'artistes
INSERT INTO ARTISTE (nomArtiste) VALUES
                                     ('The Rockers'),
                                     ('Metal Fury'),
                                     ('Blues Brothers'),
                                     ('Jazz Masters'),
                                     ('Pop Stars'),
                                     ('Heavy Hitters'),
                                     ('Smooth Vibes');

-- Association des artistes aux spectacles (table JOUER) pour plusieurs groupes par spectacle
INSERT INTO JOUER (idSpectacle, idArtiste) VALUES
                                               (1, 1),  -- The Rockers jouent dans "The Classic Rock Show"
                                               (1, 6),  -- Heavy Hitters jouent aussi dans "The Classic Rock Show"
                                               (2, 2),  -- Metal Fury joue dans "Metal Overdrive"
                                               (2, 6),  -- Heavy Hitters jouent aussi dans "Metal Overdrive"
                                               (3, 3),  -- Blues Brothers jouent dans "Blues Revival"
                                               (4, 4),  -- Jazz Masters jouent dans "Smooth Jazz Evening"
                                               (4, 7),  -- Smooth Vibes jouent aussi dans "Smooth Jazz Evening"
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
                                                        ('admin_user', 'admin@example.com', '$2y$12$.0RaLeuDaTsLZ.Uwlaz4bu5OqgXhmkwiWWHrl/SWm/94pc5HrMiBC', 3),
                                                        ('staff_user', 'staff@example.com', '$2y$12$Nem3hvBl/U9fLJp4IB/TuOUbuqgnlwAr6XfGC8oSZ8Gvirmz3pBfu', 2),
                                                        ('standard_user', 'user@example.com', '$2y$12$SugwzyylTBY/M9tEEwE6JeO4wAOes2bw3dQKU1fuoWDRS3tttGU2i', 1);

-- Insertion de préférences utilisateurs (table PREFERENCE)
INSERT INTO PREFERENCE (idUser, idSpectacle) VALUES
                                                 (3, 1),  -- standard_user a ajouté "The Classic Rock Show" à ses préférences
                                                 (3, 3),  -- standard_user a ajouté "Blues Revival" à ses préférences
                                                 (2, 2);  -- staff_user a ajouté "Metal Overdrive" à ses préférences
