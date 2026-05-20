-- Requêtes SQL de vérification du bon fonctionnement de la base
-- A exécuter dans l'onglet "SQL" de phpMyAdmin

USE adoption;

-- 1) Lister tous les animaux disponibles avec leur catégorie et leur propriétaire
SELECT a.id, a.nom, c.nom AS categorie, a.age, a.sexe, a.statut, u.nom AS proprietaire
FROM animaux a
JOIN categories c ON c.id = a.categorie_id
JOIN utilisateurs u ON u.id = a.proprietaire_id
WHERE a.statut = 'disponible';

-- 2) Compter le nombre d'animaux par catégorie
SELECT c.nom AS categorie, COUNT(a.id) AS nb_animaux
FROM categories c
LEFT JOIN animaux a ON a.categorie_id = c.id
GROUP BY c.id;

-- 3) Voir toutes les demandes d'adoption en attente avec le nom du client et de l'animal
SELECT d.id, d.date_demande, u.nom AS client, a.nom AS animal, d.statut
FROM demandes_adoption d
JOIN utilisateurs u ON u.id = d.utilisateur_id
JOIN animaux a ON a.id = d.animal_id
WHERE d.statut = 'en_attente';

-- 4) Voir les favoris d'un utilisateur (exemple : utilisateur 4)
SELECT f.id, a.nom AS animal, f.commentaire_personnel
FROM favoris f
JOIN animaux a ON a.id = f.animal_id
WHERE f.utilisateur_id = 4;

-- 5) Vérifier la contrainte UNIQUE sur l'email (doit échouer si on relance)
-- INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES ('Doublon', 'admin@test.fr', 'x', 'client');

-- 6) Lister les utilisateurs et leur nombre d'animaux proposés
SELECT u.nom, u.email, u.role, COUNT(a.id) AS nb_animaux_proposes
FROM utilisateurs u
LEFT JOIN animaux a ON a.proprietaire_id = u.id
GROUP BY u.id;
