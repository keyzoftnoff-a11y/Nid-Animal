-- Base de données du site d'adoption
-- A importer dans phpMyAdmin

DROP DATABASE IF EXISTS adoption;
CREATE DATABASE adoption CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE adoption;

-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'gestionnaire', 'client') NOT NULL DEFAULT 'client',
    actif TINYINT(1) NOT NULL DEFAULT 1
);

-- Table des catégories d'animaux
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE
);

-- Table des animaux
CREATE TABLE animaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    categorie_id INT NOT NULL,
    description TEXT NOT NULL,
    age INT NOT NULL,
    sexe ENUM('M', 'F') NOT NULL DEFAULT 'M',
    statut ENUM('disponible', 'en_cours', 'adopte') NOT NULL DEFAULT 'disponible',
    photo VARCHAR(100) DEFAULT NULL,
    proprietaire_id INT NOT NULL,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (proprietaire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Table des favoris (avec commentaire personnel)
CREATE TABLE favoris (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    animal_id INT NOT NULL,
    commentaire_personnel TEXT,
    UNIQUE (utilisateur_id, animal_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animaux(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Table des demandes d'adoption (avec commentaire du gestionnaire)
CREATE TABLE demandes_adoption (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    animal_id INT NOT NULL,
    date_demande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'validee', 'refusee') NOT NULL DEFAULT 'en_attente',
    commentaire_gestion TEXT,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animaux(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Comptes de test (mot de passe pour tous : 1234)
INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
('Admin', 'admin@test.fr', '$2y$12$yeU4xQs71gXmGdXHLMfj8uLL7iMNSZM6JI9Yw1i5xTQSiDB4i3JHu', 'admin'),
('Gestion', 'gestion@test.fr', '$2y$12$yeU4xQs71gXmGdXHLMfj8uLL7iMNSZM6JI9Yw1i5xTQSiDB4i3JHu', 'gestionnaire'),
('Client', 'client@test.fr', '$2y$12$yeU4xQs71gXmGdXHLMfj8uLL7iMNSZM6JI9Yw1i5xTQSiDB4i3JHu', 'client'),
('Sophie', 'sophie@test.fr', '$2y$12$yeU4xQs71gXmGdXHLMfj8uLL7iMNSZM6JI9Yw1i5xTQSiDB4i3JHu', 'client');

INSERT INTO categories (nom) VALUES ('Chiens'), ('Chats'), ('Chèvres'), ('Lapins');

INSERT INTO animaux (nom, categorie_id, description, age, sexe, statut, photo, proprietaire_id) VALUES
('Rex', 1, 'Chien très affectueux et joueur.', 3, 'M', 'disponible', 'rex.jpg', 3),
('Mistigri', 2, 'Chat calme cherche famille tranquille.', 5, 'M', 'disponible', 'mistigri.jpg', 3),
('Bichette', 3, 'Petite chèvre joueuse.', 1, 'F', 'disponible', 'bichette.jpg', 4),
('Caramel', 4, 'Lapin nain très doux.', 1, 'F', 'disponible', 'caramel.jpg', 4),
('Max', 1, 'Beagle très dynamique et curieux.', 2, 'M', 'disponible', 'beagle.jpg', 3),
('Rocky', 1, 'Husky sibérien amical aux yeux bleus.', 4, 'M', 'disponible', 'husky.jpg', 3),
('Félix', 2, 'Chat joueur qui adore les câlins.', 1, 'M', 'disponible', 'felix.jpg', 3),
('Minette', 2, 'Petite chatte élégante et calme.', 2, 'F', 'disponible', 'minette.jpg', 3),
('Gribouille', 4, 'Lapin très curieux et gourmand.', 1, 'M', 'disponible', 'gribouille.jpg', 4);

-- Un favori de test (pour peupler toutes les tables)
INSERT INTO favoris (utilisateur_id, animal_id, commentaire_personnel) VALUES
(3, 2, 'Un chat vraiment magnifique !');

-- Une demande de test
INSERT INTO demandes_adoption (utilisateur_id, animal_id, statut) VALUES
(4, 1, 'en_attente');
