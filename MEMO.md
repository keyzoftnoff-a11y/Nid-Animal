# MÉMO pour la présentation au prof

Lis ce fichier matin de la soutenance. Tu sauras répondre à 90% des questions.

---

## 🔑 Concepts à connaître (4 mots clés)

### 1. PHP procédural
PHP s'écrit **dans la page HTML**, entre `<?php` et `?>`. Le serveur exécute le PHP, puis envoie le HTML final au navigateur. C'est ça "dynamique" : le HTML est construit en direct.

### 2. PDO
**P**HP **D**ata **O**bjects = la manière moderne de parler à MySQL en PHP. Toujours utilisé dans `config.php`.
```php
$pdo = new PDO("mysql:host=localhost;dbname=adoption", "root", "root");
```

### 3. Requête préparée
Au lieu de mettre les valeurs directement dans la requête (= dangereux, **injection SQL**), on met des `?` puis on passe les valeurs séparément :
```php
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
$stmt->execute([$email]);
```
→ Si quelqu'un tape du SQL méchant dans le champ email, PDO le traite comme du texte normal.

### 4. Session
PHP garde des infos en mémoire entre les pages dans la variable `$_SESSION`. On démarre avec `session_start()`. On enregistre l'utilisateur connecté dedans.

---

## 📄 Explication fichier par fichier

### `config.php`
- Démarre la session
- Crée la connexion PDO à la base `adoption`
- Inclus dans toutes les pages (`require 'config.php';`)

### `header.php` / `footer.php`
Le haut et le bas de chaque page (navbar + fin HTML). Évite de recopier le menu sur chaque page. La navbar change selon le rôle dans la session.

### `style.css`
Couleurs et petits effets perso. Bootstrap fait 95% du travail, ce fichier ajoute juste du goût.

### `inscription.php`
- Affiche un formulaire
- Si POST : on hash le mot de passe avec `password_hash()`, on l'enregistre
- Sécurité : on n'enregistre JAMAIS le mot de passe en clair

### `connexion.php`
- Cherche l'utilisateur par email
- `password_verify()` compare le mot de passe tapé avec le hash de la base
- Si OK : on stocke `user_id`, `nom`, `role` dans `$_SESSION`

### `deconnexion.php`
- `session_destroy()` efface la session
- Redirige vers l'accueil

### `index.php`
- Affiche tous les animaux disponibles (cartes Bootstrap)
- Recherche (champ `q` en GET) + filtre catégorie
- La requête SQL est construite morceau par morceau selon les filtres choisis

### `animal.php`
- Récupère un animal par son ID (`?id=X` dans l'URL)
- Affiche les détails (jointure avec catégorie + propriétaire)
- Si le visiteur est un client : formulaire pour ajouter aux favoris

### `proposer.php`
- Bloque les non-clients (redirection)
- Formulaire qui insère un animal dans la base
- Le `proprietaire_id` = l'utilisateur connecté (`$_SESSION['user_id']`)

### `profil.php`
- Bloque les non-clients
- 3 sections : infos perso, animaux proposés, demandes envoyées, favoris
- Boutons sur chaque favori : modifier commentaire / retirer / faire une demande

### `gestionnaire.php`
- Bloque les non-gestionnaires
- Liste les demandes "en_attente"
- Boutons Valider / Refuser + champ commentaire
- Si valider : statut de l'animal passe à "adopte" (mise à jour automatique)

### `admin.php`
- Bloque les non-admins
- Ajout / suppression de catégories
- Liste des utilisateurs + bouton Activer/Désactiver

### `database.sql`
- Crée 5 tables avec leurs clés primaires et clés étrangères
- `ON DELETE CASCADE` = si on supprime un utilisateur, ses animaux sont supprimés aussi
- Insère un jeu de test (4 users, 4 catégories, 4 animaux, 1 demande)

---

## 🛡️ Sécurité — questions probables du prof

**Q : Comment tu protèges contre l'injection SQL ?**
R : J'utilise des requêtes préparées avec PDO. Les valeurs passent par `execute([$valeur])`, jamais directement dans la chaîne SQL.

**Q : Comment tu stockes les mots de passe ?**
R : Avec `password_hash()` qui utilise bcrypt. On ne peut pas lire le mot de passe d'origine, même en regardant la base.

**Q : Comment tu protèges contre le XSS ?**
R : Toutes les sorties HTML passent par `htmlspecialchars()`. Si quelqu'un met `<script>` dans un commentaire, c'est affiché comme du texte, pas exécuté.

**Q : Comment tu gères les rôles ?**
R : Au début de chaque page protégée, je vérifie `$_SESSION['role']`. Si le rôle ne correspond pas, je redirige avec `header('Location: index.php')`.

---

## 🗄️ Base de données — questions probables

**Q : Pourquoi `FOREIGN KEY` ?**
R : Pour lier les tables. Exemple : `animaux.categorie_id` pointe vers `categories.id`. Ça empêche d'avoir un animal avec une catégorie qui n'existe pas.

**Q : Que veut dire `ON DELETE CASCADE` ?**
R : Si je supprime un utilisateur, toutes ses lignes liées (animaux, favoris, demandes) sont supprimées automatiquement.

**Q : Pourquoi `UNIQUE` sur l'email ?**
R : Pour empêcher deux utilisateurs d'avoir le même email.

**Q : C'est quoi `ENUM` ?**
R : Un type qui n'accepte qu'une liste de valeurs précises. Exemple : `statut ENUM('disponible','en_cours','adopte')` empêche d'écrire n'importe quoi.

---

## 🧠 Si le prof dit "explique-moi cette ligne"

### `$_POST['nom']`
"`$_POST` est un tableau qui contient toutes les données envoyées par un formulaire en méthode POST. Je récupère le champ qui s'appelait `nom`."

### `$_GET['id']`
"`$_GET` contient les paramètres dans l'URL. Si l'URL est `animal.php?id=3`, alors `$_GET['id']` vaut `3`."

### `$_SESSION['role']`
"`$_SESSION` est un tableau qui survit entre les pages. J'y ai mis le rôle de l'utilisateur quand il s'est connecté."

### `$stmt = $pdo->prepare(...)` puis `$stmt->execute([...])`
"Je prépare la requête avec des points d'interrogation. Puis je l'exécute en donnant les valeurs dans un tableau. PDO les remplace en toute sécurité."

### `header("Location: index.php"); exit;`
"`header` envoie une instruction au navigateur pour rediriger. `exit` arrête le script tout de suite après pour éviter qu'il continue à exécuter du code."

### `htmlspecialchars($texte)`
"Transforme les caractères dangereux comme `<` en `&lt;`. Ça empêche que du HTML ou du JS injecté soit interprété par le navigateur."

---

## ✅ Ordre de la démo conseillée

1. Page d'accueil → montre les filtres
2. Clique sur une fiche animal
3. Inscription d'un nouveau client
4. Connexion → ajout favori → demande d'adoption
5. Déconnexion → connexion en `gestion@test.fr` → valide la demande
6. Déconnexion → connexion en `admin@test.fr` → ajoute une catégorie → désactive un user

## Comptes
- admin@test.fr / 1234
- gestion@test.fr / 1234
- client@test.fr / 1234
- sophie@test.fr / 1234
