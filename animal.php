<?php
require 'config.php';

// Sécurité : on vérifie si l'ID est bien fourni dans l'URL pour éviter un avertissement "index indéfini"
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}
$message = '';

// Si le client envoie le formulaire, on ajoute aux favoris
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['role']) && $_SESSION['role'] === 'client') {
    $commentaire = $_POST['commentaire'];
    $stmt = $pdo->prepare("INSERT INTO favoris (utilisateur_id, animal_id, commentaire_personnel) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $id, $commentaire]);
    $message = "Ajouté aux favoris !";
}

// On récupère les infos de l'animal
$stmt = $pdo->prepare("SELECT a.*, c.nom AS cat_nom, u.nom AS proprio_nom
                       FROM animaux a
                       JOIN categories c ON c.id = a.categorie_id
                       JOIN utilisateurs u ON u.id = a.proprietaire_id
                       WHERE a.id = ?");
$stmt->execute([$id]);
$animal = $stmt->fetch();

// Sécurité : si l'animal n'existe pas (ex: ID bidon dans l'URL), on redirige proprement
// vers l'accueil au lieu de faire crasher la page (critère 3.2 de la grille)
if (!$animal) {
    header("Location: index.php");
    exit;
}

include 'header.php';
?>
<a href="index.php" class="btn btn-link">← Retour</a>

<h1><?= htmlspecialchars($animal['nom']) ?></h1>

<div class="card mb-4">
    <div class="row g-0">
        <?php if ($animal['photo']): ?>
            <div class="col-md-4">
                <img src="images/<?= htmlspecialchars($animal['photo']) ?>" style="width:100%; height:100%; object-fit:cover; object-position:center; border-radius:8px 0 0 8px;">
            </div>
        <?php endif; ?>
        <div class="col-md-8">
            <div class="card-body">
                <p>
                    <strong>Catégorie :</strong> <?= htmlspecialchars($animal['cat_nom']) ?><br>
                    <strong>Âge :</strong> <?= $animal['age'] ?> ans<br>
                    <strong>Sexe :</strong> <?= $animal['sexe'] == 'M' ? 'Mâle' : 'Femelle' ?><br>
                    <strong>Statut :</strong> <?= htmlspecialchars($animal['statut']) ?><br>
                    <strong>Proposé par :</strong> <?= htmlspecialchars($animal['proprio_nom']) ?>
                </p>
                <p><?= htmlspecialchars($animal['description']) ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'client'): ?>
    <h2>Ajouter à mes favoris</h2>
    <form method="post" class="col-md-6">
        <textarea name="commentaire" class="form-control mb-2" placeholder="Mon commentaire"></textarea>
        <button type="submit" class="btn btn-success">Ajouter</button>
    </form>
<?php endif; ?>

<?php include 'footer.php'; ?>
