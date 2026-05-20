<?php
require 'config.php';

// Seul un client connecté peut proposer un animal
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: index.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $cat = $_POST['categorie_id'];
    $desc = $_POST['description'];
    $age = $_POST['age'];
    $sexe = $_POST['sexe'];

    if ($nom == '' || $cat == '' || $desc == '' || $age == '') {
        $message = "Tous les champs sont obligatoires.";
    } elseif (!is_numeric($age) || intval($age) < 0) {
        // Sécurité : on s'assure que l'âge est un nombre entier positif (critère 7.6)
        $message = "L'âge doit être un nombre entier positif.";
    } elseif (!in_array($sexe, ['M', 'F'])) {
        // Sécurité : on valide les valeurs autorisées pour le sexe (critère 7.6)
        $message = "Le sexe saisi est invalide.";
    } else {
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
