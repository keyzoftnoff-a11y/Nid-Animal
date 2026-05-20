<?php
// On inclut la configuration pour la connexion à la base de données et le démarrage de session
require 'config.php';
$erreur = '';

// On vérifie si l'utilisateur a envoyé le formulaire via la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données saisies dans les champs du formulaire
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mdp = $_POST['mot_de_passe'];

    // LOGIQUE DE VALIDATION (Critère 7.6) : On s'assure que tous les champs obligatoires sont remplis
    if ($nom == '' || $email == '' || $mdp == '') {
        $erreur = "Tous les champs sont obligatoires.";
    } 
    // SÉCURITÉ & VALIDATION (Critère 7.6) : On valide le format de l'adresse email côté serveur.
    // Cela empêche l'injection de formats d'emails invalides si l'utilisateur contourne la validation HTML5.
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Le format de l'adresse email est invalide.";
    } else {
        // SÉCURITÉ (Critère 7.2) : On vérifie si l'adresse email est déjà prise en BDD.
        // On utilise une requête préparée avec un placeholder "?" pour éviter les injections SQL.
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);

        // Si fetch() renvoie un résultat, cela signifie que l'email est déjà enregistré en BDD
        if ($stmt->fetch()) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            // SÉCURITÉ (Critère 7.1) : Hachage sécurisé du mot de passe.
            // On ne stocke JAMAIS de mot de passe en clair. L'algorithme PASSWORD_DEFAULT utilise bcrypt.
            // Il génère une empreinte unique (hash) d'environ 60 caractères.
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            
            // Insertion du nouvel utilisateur en base de données avec le rôle par défaut 'client'
            // On utilise à nouveau une requête préparée sécurisée.
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, 'client')");
            $stmt->execute([$nom, $email, $hash]);
            
            // Redirection de l'utilisateur vers la page de connexion après son inscription réussie
            header("Location: connexion.php");
            exit; // Arrêt du script après redirection
        }
    }
}

// On inclut le gabarit d'en-tête commun
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
