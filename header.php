<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Adopte-Moi</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="images/logo.png" alt="Nid d'Animal" class="logo-nav">
        </a>
        <div>
            <a href="index.php" class="btn btn-link">Accueil</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'client'): ?>
                    <a href="proposer.php" class="btn btn-link">Proposer</a>
                    <a href="profil.php" class="btn btn-link">Profil</a>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'gestionnaire'): ?>
                    <a href="gestionnaire.php" class="btn btn-link">Demandes</a>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php" class="btn btn-link">Admin</a>
                <?php endif; ?>
                <span class="texte-bonjour">Bonjour <?= htmlspecialchars($_SESSION['nom']) ?></span>
                <a href="deconnexion.php" class="btn btn-link">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php" class="btn btn-link">Connexion</a>
                <a href="inscription.php" class="btn btn-primary btn-sm ms-2">Inscription</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
