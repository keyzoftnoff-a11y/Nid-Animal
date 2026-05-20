<?php
require 'config.php';

// Seul un gestionnaire peut accéder à cette page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'gestionnaire') {
    header("Location: index.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sécurité : on s'assure que les variables POST existent pour éviter les avertissements d'index indéfinis
    $id = $_POST['demande_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $comm = $_POST['commentaire_gestion'] ?? '';

    if ($action == 'valider') {
        // Logique : on récupère l'animal lié pour vérifier s'il n'est pas déjà adopté
        $stmt = $pdo->prepare("SELECT d.animal_id, a.nom AS animal_nom, a.statut AS animal_statut 
                               FROM demandes_adoption d 
                               JOIN animaux a ON a.id = d.animal_id 
                               WHERE d.id = ?");
        $stmt->execute([$id]);
        $details = $stmt->fetch();

        if ($details) {
            $animalId = $details['animal_id'];

            if ($details['animal_statut'] === 'adopte') {
                // Logique : empêche de valider une adoption si l'animal est déjà adopté par un autre client (critère 6.3)
                $message = "Erreur : Cet animal (" . htmlspecialchars($details['animal_nom']) . ") a déjà été adopté. Impossible de valider.";
            } else {
                // 1) On met la demande courante en "validée"
                $stmt = $pdo->prepare("UPDATE demandes_adoption SET statut = 'validee', commentaire_gestion = ? WHERE id = ?");
                $stmt->execute([$comm, $id]);

                // 2) On met automatiquement le statut de l'animal à "adopté" (critère 6.3)
                $stmt = $pdo->prepare("UPDATE animaux SET statut = 'adopte' WHERE id = ?");
                $stmt->execute([$animalId]);

                // 3) Logique de confort : on refuse automatiquement toutes les autres demandes encore en attente pour cet animal
                $stmt = $pdo->prepare("UPDATE demandes_adoption SET statut = 'refusee', commentaire_gestion = 'Animal déjà adopté par une autre personne.' WHERE animal_id = ? AND statut = 'en_attente' AND id != ?");
                $stmt->execute([$animalId, $id]);

                $message = "Demande validée. L'animal est adopté et les autres demandes en attente ont été refusées.";
            }
        } else {
            $message = "Demande introuvable.";
        }
    }
    if ($action == 'refuser') {
        $stmt = $pdo->prepare("UPDATE demandes_adoption SET statut = 'refusee', commentaire_gestion = ? WHERE id = ?");
        $stmt->execute([$comm, $id]);
        $message = "Demande refusée.";
    }
}

$demandes = $pdo->query("SELECT d.*, u.nom AS user_nom, a.nom AS animal_nom
                         FROM demandes_adoption d
                         JOIN utilisateurs u ON u.id = d.utilisateur_id
                         JOIN animaux a ON a.id = d.animal_id
                         WHERE d.statut = 'en_attente'")->fetchAll();

include 'header.php';
?>
<h1>Demandes en attente</h1>

<?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>

<?php foreach ($demandes as $d): ?>
    <div class="card mb-3"><div class="card-body">
        <p>
            <strong>Date :</strong> <?= $d['date_demande'] ?><br>
            <strong>Client :</strong> <?= htmlspecialchars($d['user_nom']) ?><br>
            <strong>Animal :</strong> <?= htmlspecialchars($d['animal_nom']) ?>
        </p>
        <form method="post">
            <input type="hidden" name="demande_id" value="<?= $d['id'] ?>">
            <textarea name="commentaire_gestion" class="form-control mb-2" placeholder="Commentaire (facultatif)"></textarea>
            <button type="submit" name="action" value="valider" class="btn btn-success">Valider</button>
            <button type="submit" name="action" value="refuser" class="btn btn-danger">Refuser</button>
        </form>
    </div></div>
<?php endforeach; ?>

<?php include 'footer.php'; ?>
