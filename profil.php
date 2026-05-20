<?php
// On inclut le fichier de configuration centralisé (connexion BDD + gestion des sessions)
require 'config.php';

// SÉCURITÉ (Critère 7.4) : Seul un utilisateur connecté ayant le rôle 'client' peut accéder à son profil
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: index.php");
    exit; // Arrêt immédiat si non autorisé
}

$uid = $_SESSION['user_id']; // Récupération de l'ID de l'utilisateur connecté
$message = '';

// On gère la soumission des différents formulaires d'actions de profil (modifier, supprimer, demander)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SÉCURITÉ (Critère 7.6) : Récupération sécurisée avec opérateur de fusion null pour éviter les warnings
    $action = $_POST['action'] ?? '';
    $favId = $_POST['favori_id'] ?? 0;

    // ACTION 1 : Modifier le commentaire d'un favori (Critère 4.5 du barème)
    if ($action == 'modifier') {
        $comm = $_POST['commentaire'] ?? '';
        // SÉCURITÉ : La requête SQL filtre sur l'id du favori ET l'utilisateur connecté (utilisateur_id = ?)
        // pour empêcher un utilisateur malveillant de modifier le commentaire du favori de quelqu'un d'autre.
        $stmt = $pdo->prepare("UPDATE favoris SET commentaire_personnel = ? WHERE id = ? AND utilisateur_id = ?");
        $stmt->execute([$comm, $favId, $uid]);
        $message = "Commentaire modifié.";
    }
    
    // ACTION 2 : Retirer un animal des favoris (Critère 4.4 du barème)
    if ($action == 'supprimer') {
        // SÉCURITÉ : On filtre également par l'id du favori ET l'id de l'utilisateur connecté.
        $stmt = $pdo->prepare("DELETE FROM favoris WHERE id = ? AND utilisateur_id = ?");
        $stmt->execute([$favId, $uid]);
        $message = "Favori supprimé.";
    }
    
    // ACTION 3 : Soumettre une demande d'adoption (Critère 4.6 du barème)
    if ($action == 'demander') {
        // SÉCURITÉ : On récupère l'animal associé au favori en s'assurant que ce favori appartient bien au client.
        $stmt = $pdo->prepare("SELECT animal_id FROM favoris WHERE id = ? AND utilisateur_id = ?");
        $stmt->execute([$favId, $uid]);
        $f = $stmt->fetch();

        if ($f) {
            $animalId = $f['animal_id'];

            // LOGIQUE MÉTIER & SÉCURITÉ (Critère 4.6) : Prévention contre les doublons.
            // On vérifie s'il n'y a pas déjà une demande 'en_attente' ou 'validee' pour ce couple utilisateur/animal.
            $check = $pdo->prepare("SELECT id FROM demandes_adoption WHERE utilisateur_id = ? AND animal_id = ? AND (statut = 'en_attente' OR statut = 'validee')");
            $check->execute([$uid, $animalId]);

            if ($check->fetch()) {
                $message = "Vous avez déjà soumis une demande d'adoption pour cet animal.";
            } else {
                // Si aucune demande active n'existe, on insère la nouvelle demande avec le statut par défaut 'en_attente'
                $stmt = $pdo->prepare("INSERT INTO demandes_adoption (utilisateur_id, animal_id, statut) VALUES (?, ?, 'en_attente')");
                $stmt->execute([$uid, $animalId]);
                $message = "Demande d'adoption envoyée avec succès !";
            }
        } else {
            $message = "Action non autorisée ou favori introuvable.";
        }
    }
}

// RÉCUPÉRATION DES INFOS UTILISATEUR :
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// SÉCURITÉ : Gestion de la persistance de session après nettoyage/reset de la base de données.
// Si l'utilisateur connecté a été supprimé de la BDD, on détruit sa session obsolète via deconnexion.php
if (!$user) {
    header("Location: deconnexion.php");
    exit;
}

// REQUÊTE 1 : Récupération des animaux que ce client a proposés à l'adoption (Critère 4.2)
$stmt = $pdo->prepare("SELECT * FROM animaux WHERE proprietaire_id = ?");
$stmt->execute([$uid]);
$mesAnimaux = $stmt->fetchAll();

// REQUÊTE 2 : Récupération de la liste des favoris du client avec le nom et le statut actuel de chaque animal (JOIN)
$stmt = $pdo->prepare("SELECT f.*, a.nom AS animal_nom, a.statut AS animal_statut
                       FROM favoris f JOIN animaux a ON a.id = f.animal_id
                       WHERE f.utilisateur_id = ?");
$stmt->execute([$uid]);
$favoris = $stmt->fetchAll();

// REQUÊTE 3 : Récupération de l'historique des demandes d'adoption émises par ce client (JOIN)
$stmt = $pdo->prepare("SELECT d.*, a.nom AS animal_nom
                       FROM demandes_adoption d JOIN animaux a ON a.id = d.animal_id
                       WHERE d.utilisateur_id = ?");
$stmt->execute([$uid]);
$demandes = $stmt->fetchAll();

// On inclut le gabarit d'en-tête commun
include 'header.php';
?>
<h1>Mon profil</h1>

<?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>

<p><strong><?= htmlspecialchars($user['nom']) ?></strong> · <?= htmlspecialchars($user['email']) ?></p>

<h2>Mes animaux proposés</h2>
<ul>
<?php foreach ($mesAnimaux as $a): ?>
    <li><a href="animal.php?id=<?= $a['id'] ?>"><?= htmlspecialchars($a['nom']) ?></a> (<?= $a['statut'] ?>)</li>
<?php endforeach; ?>
</ul>

<h2>Mes demandes</h2>
<table class="table">
    <tr><th>Animal</th><th>Date</th><th>Statut</th><th>Commentaire</th></tr>
    <?php foreach ($demandes as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['animal_nom']) ?></td>
            <td><?= $d['date_demande'] ?></td>
            <td><?= $d['statut'] ?></td>
            <td><?= htmlspecialchars($d['commentaire_gestion'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Mes favoris</h2>
<?php foreach ($favoris as $f): ?>
    <div class="card mb-2"><div class="card-body">
        <h5><?= htmlspecialchars($f['animal_nom']) ?> (<?= $f['animal_statut'] ?>)</h5>

        <form method="post" class="mb-2">
            <input type="hidden" name="favori_id" value="<?= $f['id'] ?>">
            <input type="hidden" name="action" value="modifier">
            <textarea name="commentaire" class="form-control mb-1"><?= htmlspecialchars($f['commentaire_personnel'] ?? '') ?></textarea>
            <button class="btn btn-sm btn-primary">Enregistrer</button>
        </form>

        <form method="post" class="d-inline">
            <input type="hidden" name="favori_id" value="<?= $f['id'] ?>">
            <input type="hidden" name="action" value="supprimer">
            <button class="btn btn-sm btn-danger">Retirer</button>
        </form>

        <form method="post" class="d-inline">
            <input type="hidden" name="favori_id" value="<?= $f['id'] ?>">
            <input type="hidden" name="action" value="demander">
            <button class="btn btn-sm btn-success">Demander adoption</button>
        </form>
    </div></div>
<?php endforeach; ?>

<?php include 'footer.php'; ?>
