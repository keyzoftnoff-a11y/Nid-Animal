# 🐾 Nid'Animal - Plateforme d'Adoption d'Animaux
> Projet académique - Module R209 : Programmation PHP / SQL / HTML (BUT Réseaux & Télécoms)

Nid'Animal est une application web dynamique permettant de gérer l'adoption d'animaux. Elle intègre un catalogue public de recherche et trois espaces connectés avec des rôles distincts (Client, Gestionnaire, Administrateur).

---

## 🚀 Installation & Lancement (MAMP / XAMPP)

1. **Placer le projet** :
   Déplacez le dossier `Nid'Animal` dans le répertoire de votre serveur web :
   * **MAMP** : `/Applications/MAMP/htdocs/` ou `C:\MAMP\htdocs\`
   * **XAMPP** : `C:\xampp\htdocs\`

2. **Importer la Base de Données** :
   * Démarrez les services **Apache** et **MySQL**.
   * Allez sur **phpMyAdmin** (`http://localhost/phpmyadmin` ou `http://localhost:8888/phpmyadmin`).
   * Créez une base de données nommée **`adoption`**.
   * Allez dans l'onglet **Importer**, choisissez le fichier [database.sql](database.sql) et cliquez sur **Exécuter**.

3. **Accéder au site** :
   * Ouvrez votre navigateur sur : `http://localhost/Nid'Animal/` (ou `http://localhost:8888/Nid'Animal/`).

> [!NOTE]
> Le fichier de configuration `config.php` intègre un système de connexion automatique compatible à la fois avec la configuration de **MAMP** (mot de passe `root`) et de **XAMPP** (mot de passe vide `""`).

---

## 👥 Comptes de Test (Mot de passe commun : `1234`)

| Rôle | Email | Description |
|------|-------|-------------|
| **Administrateur** | `admin@test.fr` | Gestion des catégories et modération des comptes utilisateurs. |
| **Gestionnaire** | `gestion@test.fr` | Validation/refus des demandes d'adoption avec commentaires. |
| **Client (Propriétaire)** | `client@test.fr` | Proposition d'animaux, gestion des favoris et demandes d'adoptions. |
| **Client** | `sophie@test.fr` | Profil client pour naviguer et adopter des animaux. |

---

## 🛠️ Fonctionnalités par Espace

### 🌍 Espace Public (Tous)
* Consultation de la liste des animaux disponibles à l'adoption avec leurs photos.
* Moteur de recherche textuel sur les annonces.
* Filtres avancés par catégorie, sexe et âge des animaux.
* Consultation de la fiche détaillée d'un animal contenant toutes ses informations.

### 👤 Espace Client (Connecté)
* Inscription, connexion sécurisée et déconnexion.
* **Proposer un animal** à l'adoption via un formulaire complet (nom, âge, sexe, catégorie, description).
* **Gestion des Favoris** : Ajout/retrait d'animaux en favoris avec possibilité d'ajouter ou modifier un commentaire personnel.
* **Demande d'adoption** : Soumission d'une demande officielle pour un animal depuis la liste des favoris.
* Suivi en temps réel de l'état de ses demandes d'adoption (En attente, Validée, Refusée) et lecture du commentaire du gestionnaire.

### 💼 Espace Gestionnaire
* Visualisation de l'ensemble des demandes d'adoption en attente.
* Validation ou refus des demandes avec saisie d'un commentaire justificatif.
* **Mise à jour automatique** du statut de l'animal en "Adopté" en cas de validation.
* **Refus automatique** de toutes les autres demandes en attente pour un même animal dès qu'une demande est validée.

### ⚙️ Espace Administrateur
* Ajout et suppression de catégories d'animaux (avec blocage si des animaux y sont encore associés).
* Visualisation de la liste des utilisateurs inscrits.
* Activation ou désactivation des comptes utilisateurs (sécurité empêchant l'administrateur de se désactiver lui-même).

---

## 🛡️ Sécurité & Bonnes Pratiques Implémentées

* **Protection contre les injections SQL** : Utilisation systématique de requêtes préparées PDO (`prepare` / `execute`) sur toutes les requêtes contenant des variables utilisateurs.
* **Protection contre les failles XSS** : Échappement systématique des données dynamiques affichées en HTML via `htmlspecialchars()`.
* **Sécurisation des mots de passe** : Hachage fort des mots de passe à l'inscription à l'aide de `password_hash()` (algorithme bcrypt) et vérification sécurisée via `password_verify()`.
* **Gestion sécurisée des Sessions** : Cookie de session configuré avec l'option `HttpOnly` et régénération de l'identifiant de session (`session_regenerate_id(true)`) après chaque connexion réussie.
