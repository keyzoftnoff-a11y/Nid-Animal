<?php
require 'config.php';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mdp = $_POST['mot_de_passe'];

    if ($nom == '' || $email == '' || $mdp == '') {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Sécurité : validation du format de l'email côté serveur (critère 7.6)
        $erreur = "Le format de l'adresse email est invalide.";
    } else {
        // On vérifie si l'email est déjà utilisé
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            // On hash le mot de passe avant de l'enregistrer
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, 'client')");
            $stmt->execute([$nom, $email, $hash]);
            header("Location: connexion.php");
            exit;
        }
    }
}
include 'header.php';
?>
<h1>Inscription</h1>

<?php if ($erreur): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<form method="post" class="col-md-6">
    <div class="mb-3">
        <label class="form-label">Nom</label>
        <input type="text" name="nom" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Mot de passe</label>
        <input type="password" name="mot_de_passe" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">S'inscrire</button>
</form>

<?php include 'footer.php'; ?>
