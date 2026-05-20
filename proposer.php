<?php
// On inclut la connexion PDO et la configuration de la session
require 'config.php';

// SÉCURITÉ (Critère 7.4) : Contrôle d'accès par rôle.
// Seul un utilisateur connecté ayant le rôle 'client' est autorisé à proposer un animal.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    // Redirection immédiate vers l'accueil si l'utilisateur n'a pas les droits
    header("Location: index.php");
    exit; // Arrêt obligatoire du script
}

// On récupère toutes les catégories de la base de données pour générer dynamiquement la liste déroulante (select)
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$message = '';

// On vérifie si l'utilisateur a soumis le formulaire via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données saisies dans les différents champs
    $nom = $_POST['nom'];
    $cat = $_POST['categorie_id'];
    $desc = $_POST['description'];
    $age = $_POST['age'];
    $sexe = $_POST['sexe'];

    // VALIDATION DES DONNÉES (Critère 7.6) : Tous les champs doivent être saisis
    if ($nom == '' || $cat == '' || $desc == '' || $age == '') {
        $message = "Tous les champs sont obligatoires.";
    } 
    // VALIDATION DES DONNÉES (Critère 7.6) : On s'assure côté serveur que l'âge est bien un entier numérique positif
    elseif (!is_numeric($age) || intval($age) < 0) {
        $message = "L'âge doit être un nombre entier positif.";
    } 
    // VALIDATION DES DONNÉES (Critère 7.6) : Validation stricte des valeurs autorisées pour le sexe ('M' ou 'F')
    elseif (!in_array($sexe, ['M', 'F'])) {
        $message = "Le sexe saisi est invalide.";
    } else {
        // SÉCURITÉ (Critère 7.2) : Insertion sécurisée via requête préparée.
        // On associe automatiquement l'animal créé au client connecté en utilisant $_SESSION['user_id'] comme proprietaire_id.
        $stmt = $pdo->prepare("INSERT INTO animaux (nom, categorie_id, description, age, sexe, statut, proprietaire_id)
                               VALUES (?, ?, ?, ?, ?, 'disponible', ?)");
        $stmt->execute([$nom, $cat, $desc, $age, $sexe, $_SESSION['user_id']]);
        
        $message = "Animal proposé !";
    }
}

include 'header.php';
?>
<h1>Proposer un animal</h1>

<?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post" class="col-md-6">
    <div class="mb-2">
        <label>Nom</label>
        <input type="text" name="nom" class="form-control">
    </div>
    <div class="mb-2">
        <label>Catégorie</label>
        <select name="categorie_id" class="form-select">
            <option value="">-- Choisir --</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-2">
        <label>Sexe</label>
        <select name="sexe" class="form-select">
            <option value="M">Mâle</option>
            <option value="F">Femelle</option>
        </select>
    </div>
    <div class="mb-2">
        <label>Âge</label>
        <input type="number" name="age" class="form-control">
    </div>
    <div class="mb-2">
        <label>Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Proposer</button>
</form>

<?php include 'footer.php'; ?>
