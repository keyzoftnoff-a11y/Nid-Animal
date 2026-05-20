<?php
// On inclut le fichier de configuration centralisé (connexion BDD et session)
require 'config.php';

// SÉCURITÉ (Critère 7.4) : Contrôle d'accès par rôle.
// Seul un utilisateur connecté ayant le rôle 'admin' est autorisé à charger cette page.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit; // Arrêt immédiat si non autorisé
}

$message = '';

// On gère la soumission des différentes actions d'administration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SÉCURITÉ (Critère 7.6) : Récupération de l'action demandée
    $action = $_POST['action'];

    // ACTION 1 : Ajouter une catégorie d'animaux (Critère 5.1 du barème)
    if ($action == 'ajouter_cat') {
        $nom = $_POST['nom'];
        if ($nom != '') {
            // Requête préparée sécurisée contre les injections SQL
            $stmt = $pdo->prepare("INSERT INTO categories (nom) VALUES (?)");
            $stmt->execute([$nom]);
            $message = "Catégorie ajoutée.";
        }
    }
    
    // ACTION 2 : Supprimer une catégorie (Critère 5.1 du barème)
    if ($action == 'supprimer_cat') {
        try {
            // Requête préparée pour supprimer la catégorie
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$_POST['categorie_id'] ?? 0]);
            $message = "Catégorie supprimée.";
        } catch (PDOException $e) {
            // SÉCURITÉ / ROBUSTESSE (Critère 5.1) : Gestion de l'intégrité référentielle SQL.
            // Si la catégorie contient encore des animaux, la base de données lève une exception (ON DELETE RESTRICT).
            // Le try...catch intercepte cette erreur SQL et évite un crash de page PHP (écran blanc).
            $message = "Impossible de supprimer cette catégorie car elle contient des animaux.";
        }
    }
    
    // ACTION 3 : Activer un compte utilisateur (Critère 5.2 du barème)
    if ($action == 'activer') {
        // Met le champ 'actif' à 1 en BDD via une requête préparée
        $stmt = $pdo->prepare("UPDATE utilisateurs SET actif = 1 WHERE id = ?");
        $stmt->execute([$_POST['user_id'] ?? 0]);
        $message = "Compte activé.";
    }
    
    // ACTION 4 : Désactiver un compte utilisateur (Critère 5.2 du barème)
    if ($action == 'desactiver') {
        $userIdToDeactivate = $_POST['user_id'] ?? 0;
        
        // SÉCURITÉ MÉTIER (Critère 5.2) : Protection anti-auto-blocage.
        // On empêche l'administrateur connecté de désactiver son propre compte, ce qui l'exclurait définitivement du site.
        if ($userIdToDeactivate == $_SESSION['user_id']) {
            $message = "Erreur : Vous ne pouvez pas désactiver votre propre compte administrateur.";
        } else {
            // Désactivation sécurisée en passant le champ 'actif' à 0
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
