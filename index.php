<?php
require 'config.php';

// On récupère toutes les catégories pour le menu de filtre
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();

// On lit les filtres tapés par l'utilisateur (méthode GET)
$recherche = $_GET['q'] ?? '';
$cat = $_GET['categorie'] ?? '';
$age = $_GET['age'] ?? '';
$sexe = $_GET['sexe'] ?? '';

// On construit la requête SQL selon les filtres
$sql = "SELECT a.*, c.nom AS cat_nom
        FROM animaux a
        JOIN categories c ON c.id = a.categorie_id
        WHERE a.statut = 'disponible'";
$params = [];

if ($recherche != '') {
    $sql .= " AND a.nom LIKE ?";
    $params[] = "%$recherche%";
}
if ($cat != '') {
    $sql .= " AND a.categorie_id = ?";
    $params[] = $cat;
}
if ($age != '') {
    $sql .= " AND a.age <= ?";
    $params[] = $age;
}
if ($sexe != '') {
    $sql .= " AND a.sexe = ?";
    $params[] = $sexe;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animaux = $stmt->fetchAll();

include 'header.php';
?>

</div><!-- fermeture du container ouvert dans header.php -->

<!-- section hero pleine largeur -->
<div class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-contenu">
        <p class="hero-sous-titre">Site d'adoption animale</p>
        <h2 class="hero-slogan">Un refuge, une rencontre,<br>une nouvelle histoire.</h2>
        <a href="#animaux" class="btn hero-btn">Découvrir les chiens</a>
    </div>
</div>

<div class="container"><!-- on réouvre le container pour le reste de la page -->

<div id="animaux" style="padding-top: 40px;">
    <h1>Animaux à adopter</h1>

    <form method="get" class="row g-2 mb-4">
        <div class="col-md-3">
            <input type="text" name="q" class="form-control" placeholder="Rechercher..." value="<?= htmlspecialchars($recherche) ?>">
        </div>
        <div class="col-md-3">
            <select name="categorie" class="form-select">
                <option value="">Toutes catégories</option>
                <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($cat == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="age" class="form-select">
                <option value="">Tous les âges</option>
                <option value="1" <?= ($age == '1') ? 'selected' : '' ?>>Moins de 1 an</option>
                <option value="2" <?= ($age == '2') ? 'selected' : '' ?>>Moins de 2 ans</option>
                <option value="3" <?= ($age == '3') ? 'selected' : '' ?>>Moins de 3 ans</option>
                <option value="5" <?= ($age == '5') ? 'selected' : '' ?>>Moins de 5 ans</option>
                <option value="10" <?= ($age == '10') ? 'selected' : '' ?>>Moins de 10 ans</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="sexe" class="form-select">
                <option value="">Tous les sexes</option>
                <option value="M" <?= ($sexe == 'M') ? 'selected' : '' ?>>Mâle</option>
                <option value="F" <?= ($sexe == 'F') ? 'selected' : '' ?>>Femelle</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
        </div>
    </form>

    <div class="row">
    <?php foreach ($animaux as $a): ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <?php if ($a['photo']): ?>
                    <img src="images/<?= htmlspecialchars($a['photo']) ?>" class="card-img-top" alt="<?= htmlspecialchars($a['nom']) ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h5><?= htmlspecialchars($a['nom']) ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($a['cat_nom']) ?> · <?= $a['age'] ?> ans · <?= $a['sexe'] == 'M' ? 'Mâle' : 'Femelle' ?></p>
                    <a href="animal.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
