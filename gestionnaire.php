<?php
// On inclut le fichier de configuration centralisé (connexion BDD et session)
require 'config.php';

// SÉCURITÉ (Critère 7.4) : Contrôle d'accès par rôle.
// Seul un utilisateur connecté ayant le rôle 'gestionnaire' est autorisé sur cette page.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'gestionnaire') {
    header("Location: index.php");
    exit; // Arrêt immédiat si non autorisé
}

$message = '';

// On gère la soumission des décisions (valider ou refuser une demande d'adoption)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SÉCURITÉ (Critère 7.6) : Récupération sécurisée pour éviter les index indéfinis en cas de POST vide/falsifié
    $id = $_POST['demande_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $comm = $_POST['commentaire_gestion'] ?? '';

    // ACTION : Validation d'une demande d'adoption (Critère 6.2 du barème)
    if ($action == 'valider') {
        // LOGIQUE MÉTIER (Critère 6.3) : On récupère l'animal associé à cette demande et son statut actuel.
        $stmt = $pdo->prepare("SELECT d.animal_id, a.nom AS animal_nom, a.statut AS animal_statut 
                               FROM demandes_adoption d 
                               JOIN animaux a ON a.id = d.animal_id 
                               WHERE d.id = ?");
        $stmt->execute([$id]);
        $details = $stmt->fetch();

        if ($details) {
            $animalId = $details['animal_id'];

            // SÉCURITÉ MÉTIER (Critère 6.3) : On vérifie si l'animal n'a pas déjà été adopté par quelqu'un d'autre
            // entre-temps pour éviter la double adoption.
            if ($details['animal_statut'] === 'adopte') {
                $message = "Erreur : Cet animal (" . htmlspecialchars($details['animal_nom']) . ") a déjà été adopté. Impossible de valider.";
            } else {
                // 1) On valide la demande d'adoption sélectionnée
                $stmt = $pdo->prepare("UPDATE demandes_adoption SET statut = 'validee', commentaire_gestion = ? WHERE id = ?");
                $stmt->execute([$comm, $id]);

                // 2) MISE À JOUR AUTOMATIQUE (Critère 6.3) : Le statut de l'animal passe instantanément à 'adopte'
                $stmt = $pdo->prepare("UPDATE animaux SET statut = 'adopte' WHERE id = ?");
                $stmt->execute([$animalId]);

                // 3) LOGIQUE DE CONFORT (Critère 6.3) : On refuse automatiquement toutes les autres demandes en attente
                // pour ce même animal avec un motif prédéfini, car il vient d'être adopté.
                $stmt = $pdo->prepare("UPDATE demandes_adoption SET statut = 'refusee', commentaire_gestion = 'Animal déjà adopté par une autre personne.' WHERE animal_id = ? AND statut = 'en_attente' AND id != ?");
                $stmt->execute([$animalId, $id]);

                $message = "Demande validée. L'animal est adopté et les autres demandes en attente ont été refusées.";
            }
        } else {
            $message = "Demande introuvable.";
        }
    }
    
    // ACTION : Refus d'une demande d'adoption (Critère 6.2 du barème)
    if ($action == 'refuser') {
        // On passe simplement le statut de la demande à 'refusee' et on enregistre le commentaire du gestionnaire
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
