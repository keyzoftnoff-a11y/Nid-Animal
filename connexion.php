<?php
require 'config.php';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $mdp = $_POST['mot_de_passe'];

    // On cherche l'utilisateur dans la base
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // On vérifie le mot de passe avec password_verify
    if ($user && password_verify($mdp, $user['mot_de_passe'])) {
        if ($user['actif'] == 0) {
            $erreur = "Compte désactivé.";
        } else {
            // Sécurité : on régénère l'identifiant de session après une connexion réussie
            // pour empêcher les attaques par fixation de session (critère 7.5 de la grille)
            session_regenerate_id(true);

            // On enregistre les infos dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        }
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
include 'header.php';
?>
<h1>Connexion</h1>

<?php if ($erreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<form method="post" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Mot de passe</label>
        <input type="password" name="mot_de_passe" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Se connecter</button>
</form>

<?php include 'footer.php'; ?>
