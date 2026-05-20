<?php
// On inclut le fichier de configuration centralisé (connexion BDD et session)
require 'config.php';

// SÉCURITÉ (Critère 7.6) : Récupération sécurisée du paramètre 'id' dans l'URL.
// Si aucun ID n'est présent dans l'URL (méthode GET), on redirige vers l'accueil pour éviter des erreurs SQL.
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}
$message = '';

// ACTION : Ajout de l'animal dans les favoris du client connecté (Critère 4.4 du barème)
// On s'assure que la requête est en POST, que l'utilisateur est connecté et qu'il est bien un client.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['role']) && $_SESSION['role'] === 'client') {
    $commentaire = $_POST['commentaire'];
    
    // SÉCURITÉ (Critère 7.2) : Insertion sécurisée via requête préparée pour empêcher les injections SQL.
    $stmt = $pdo->prepare("INSERT INTO favoris (utilisateur_id, animal_id, commentaire_personnel) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $id, $commentaire]);
    
    $message = "Ajouté aux favoris !";
}

// RÉCUPÉRATION DES INFOS DÉTAILLÉES DE L'ANIMAL (Critère 3.2 du barème) :
// On réalise deux jointures SQL (JOIN) pour récupérer en même temps :
// - Le nom de la catégorie (ex: 'Chien' au lieu de categorie_id = 1)
// - Le nom du propriétaire (qui a proposé l'animal)
$stmt = $pdo->prepare("SELECT a.*, c.nom AS cat_nom, u.nom AS proprio_nom
                       FROM animaux a
                       JOIN categories c ON c.id = a.categorie_id
                       JOIN utilisateurs u ON u.id = a.proprietaire_id
                       WHERE a.id = ?");
$stmt->execute([$id]);
$animal = $stmt->fetch(); // Récupère les données de l'animal ou false s'il n'existe pas

// SÉCURITÉ (Critère 3.2) : Gestion d'un ID d'animal inexistant.
// Si un utilisateur saisit un ID invalide manuellement dans l'URL, on le redirige vers l'accueil au lieu d'afficher une page blanche.
if (!$animal) {
    header("Location: index.php");
    exit;
}

// On inclut le gabarit d'en-tête commun
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
