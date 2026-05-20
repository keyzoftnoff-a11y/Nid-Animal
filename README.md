# Site adoption d'animaux - R209

## Installation
1. Mettre le dossier dans `/Applications/MAMP/htdocs/` (MAMP).
2. Démarrer MAMP (Apache + MySQL).
3. phpMyAdmin → onglet **Importer** → choisir `database.sql` → Exécuter.
4. Ouvrir `http://localhost:8888/r209-adoption/`.

## Comptes de test (mot de passe : `1234`)
- `admin@test.fr` → administrateur
- `gestion@test.fr` → gestionnaire
- `client@test.fr` → client
- `sophie@test.fr` → cliente

## Fichiers du projet
- `database.sql` → schéma + données de test
- `requetes_verification.sql` → requêtes pour vérifier la base
- `SCHEMA.md` → schéma E-A (Merise)
- `config.php` → connexion PDO + session
- `header.php` / `footer.php` → gabarit commun
- `style.css` → style personnalisé
- `inscription.php` / `connexion.php` / `deconnexion.php` → authentification
- `index.php` → catalogue + recherche + filtres
- `animal.php` → fiche animal + ajout favoris
- `proposer.php` → formulaire de proposition (client)
- `profil.php` → profil + animaux + favoris + demandes (client)
- `gestionnaire.php` → validation/refus + commentaire (gestionnaire)
- `admin.php` → catégories + activation/désactivation utilisateurs (admin)
