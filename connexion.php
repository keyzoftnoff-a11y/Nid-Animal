<?php
// On inclut le fichier de configuration (connexion BDD + démarrage de session sécurisée)
require 'config.php';
$erreur = '';

// On vérifie si le formulaire a été soumis via la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données saisies par l'utilisateur
    $email = $_POST['email'];
    $mdp = $_POST['mot_de_passe'];

    // SÉCURITÉ (Critère 7.2) : Utilisation d'une requête préparée pour éviter les injections SQL.
    // Le "?" est un espace réservé (placeholder) qui sera remplacé de manière sécurisée par l'email.
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    
    // Exécution de la requête en transmettant l'email dans un tableau
    $stmt->execute([$email]);
    
    // Récupération de l'utilisateur sous forme de tableau associatif (ou false s'il n'existe pas)
    $user = $stmt->fetch();

    // SÉCURITÉ (Critère 7.1) : Vérification du mot de passe avec password_verify().
    // Cette fonction compare le mot de passe en clair saisi avec le hash sécurisé stocké en base.
    if ($user && password_verify($mdp, $user['mot_de_passe'])) {
        
        // LOGIQUE MÉTIER : On vérifie si le compte de l'utilisateur n'est pas désactivé par l'admin
        if ($user['actif'] == 0) {
            $erreur = "Compte désactivé.";
        } else {
            // SÉCURITÉ (Critère 7.5) : Régénération de l'identifiant de session après connexion.
            // Cela empêche les attaques par fixation de session (vol de session).
            session_regenerate_id(true);

            // Stockage des informations clés de l'utilisateur connecté dans la session globale ($_SESSION)
            $_SESSION['user_id'] = $user['id'];  // ID unique pour les requêtes futures (ex: favoris, propositions)
            $_SESSION['nom'] = $user['nom'];      // Nom affiché sur l'interface
            $_SESSION['role'] = $user['role'];    // Rôle (client, gestionnaire, admin) pour le contrôle d'accès
            
            // Redirection vers la page d'accueil du site après connexion réussie
            header("Location: index.php");
            exit; // On arrête l'exécution du script pour valider la redirection
        }
    } else {
        // Message d'erreur générique pour ne pas indiquer si c'est l'email ou le mot de passe qui est faux (bonne pratique)
        $erreur = "Email ou mot de passe incorrect.";
    }
}

// On inclut le gabarit d'en-tête commun (barre de navigation adaptée au rôle de la session)
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
