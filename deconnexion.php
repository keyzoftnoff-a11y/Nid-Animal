<?php
// On démarre ou récupère la session en cours
session_start();

// SÉCURITÉ (Critère 4.1) : On détruit toutes les variables de session et la session elle-même.
// Cela déconnecte l'utilisateur en effaçant son rôle, son ID et son nom du serveur.
session_destroy();

// Redirection immédiate vers la page d'accueil publique
header("Location: index.php");
exit; // Fin du script
?>
