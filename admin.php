<?php
require 'config.php';

// Seul un administrateur peut accéder à cette page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action == 'ajouter_cat') {
        $nom = $_POST['nom'];
        if ($nom != '') {
            $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (?)");
            $stmt->execute([$nom]);
            $message = "Catégorie ajoutée.";
        }
    }
    if ($action == 'supprimer_cat') {
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$_POST['categorie_id'] ?? 0]);
            $message = "Catégorie supprimée.";
        } catch (PDOException $e) {
            // Sécurité/Logique : si la catégorie est liée à des animaux (ON DELETE RESTRICT),
            // on attrape l'exception PDO au lieu de faire crasher la page en blanc (critère 5.1)
            $message = "Impossible de supprimer cette catégorie car elle contient des animaux.";
        }
    }
    if ($action == 'activer') {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET actif = 1 WHERE id = ?");
        $stmt->execute([$_POST['user_id'] ?? 0]);
        $message = "Compte activé.";
    }
    if ($action == 'desactiver') {
        $userIdToDeactivate = $_POST['user_id'] ?? 0;
        
        // Logique : on vérifie que l'admin connecté ne désactive pas son propre compte (critère 5.2)
        if ($userIdToDeactivate == $_SESSION['user_id']) {
            $message = "Erreur : Vous ne pouvez pas désactiver votre propre compte administrateur.";
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET actif = 0 WHERE id = ?");
            $stmt->execute([$userIdToDeactivate]);
            $message = "Compte désactivé.";
        }
    }
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs")->fetchAll();

include 'header.php';
?>
<h1>Espace administrateur</h1>

<?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>

<h2>Catégories</h2>

<form method="post" class="row g-2 mb-3 col-md-6">
    <input type="hidden" name="action" value="ajouter_cat">
    <div class="col-md-8"><input type="text" name="nom" class="form-control" placeholder="Nouvelle catégorie"></div>
    <div class="col-md-4"><button class="btn btn-primary w-100">Ajouter</button></div>
</form>

<table class="table">
    <?php foreach ($categories as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['nom']) ?></td>
            <td>
                <form method="post" class="d-inline">
                    <input type="hidden" name="action" value="supprimer_cat">
                    <input type="hidden" name="categorie_id" value="<?= $c['id'] ?>">
                    <button class="btn btn-sm btn-danger">Supprimer</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Utilisateurs</h2>
<table class="table">
    <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Action</th></tr>
    <?php foreach ($utilisateurs as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['nom']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['role'] ?></td>
            <td><?= $u['actif'] ? 'Actif' : 'Désactivé' ?></td>
            <td>
                <form method="post" class="d-inline">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <?php if ($u['actif']): ?>
                        <input type="hidden" name="action" value="desactiver">
                        <button class="btn btn-sm btn-warning">Désactiver</button>
                    <?php else: ?>
                        <input type="hidden" name="action" value="activer">
                        <button class="btn btn-sm btn-success">Activer</button>
                    <?php endif; ?>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include 'footer.php'; ?>
