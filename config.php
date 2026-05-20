<?php
// Configuration sécurisée des cookies de session (protection contre le vol de session via faille XSS)
session_set_cookie_params([
    'lifetime' => 0,          // Le cookie expire à la fermeture du navigateur
    'path' => '/',            // Accessible sur l'ensemble du site
    'domain' => '',           // Domaine par défaut
    'secure' => false,        // Mettre à true si le site utilise HTTPS (localhost est souvent en HTTP simple)
    'httponly' => true,       // Empêche l'accès au cookie via JavaScript (critère 7.5 de la grille)
    'samesite' => 'Lax'       // Protection contre les requêtes intersites CSRF
]);

// On démarre la session
session_start();

// On se connecte à la base de données avec PDO (avec un mécanisme de repli pour fonctionner sur MAMP et XAMPP)
try {
    // Tentative 1 : mot de passe 'root' (MAMP / configuration du prof)
    $pdo = new PDO("mysql:host=localhost;dbname=adoption;charset=utf8mb4", "root", "root");
} catch (PDOException $e) {
    // Tentative 2 : mot de passe vide (XAMPP par défaut)
    $pdo = new PDO("mysql:host=localhost;dbname=adoption;charset=utf8mb4", "root", "");
}
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
