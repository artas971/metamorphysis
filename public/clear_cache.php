<?php
$secret = 'meta_clear_2026';
if (!isset($_GET['token']) || $_GET['token'] !== $secret) {
    http_response_code(403);
    die('Accès refusé.');
}

header('Content-Type: text/plain; charset=utf-8');

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

// Mise à jour Git sur la production si demandé
if (isset($_GET['git_pull'])) {
    echo "=== EXÉCUTION GIT PULL SUR LE SERVEUR ===\n";
    $output = shell_exec('git pull origin main 2>&1');
    echo $output . "\n\n";
}

// Mise à jour de la base de données sur la production si demandé
if (isset($_GET['update_db'])) {
    echo "=== MISE À JOUR BASE DE DONNÉES (DOCTRINE) ===\n";
    $output = shell_exec('php ../bin/console doctrine:schema:update --force 2>&1');
    echo $output . "\n\n";
}

// Nettoyage de la séance orpheline si demandé
if (isset($_GET['clean_seance'])) {
    try {
        $envFile = __DIR__ . '/../.env.local';
        if (!file_exists($envFile)) $envFile = __DIR__ . '/../.env';
        $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';
        
        if (preg_match('/DATABASE_URL="?mysql:\/\/([^:]+):([^@]+)@([^:\/]+)(?::(\d+))?\/([^"?\s]+)/', $envContent, $m)) {
            $pdo = new PDO("mysql:host={$m[3]};port=" . ($m[4] ?: '3306') . ";dbname={$m[5]};charset=utf8mb4", $m[1], $m[2], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Suppression des séances orphelines dont le client n'existe plus dans la table user
            $deleted = $pdo->exec("DELETE FROM seance WHERE user_id NOT IN (SELECT id FROM `user`)");
            echo ">>> ACTION EFFECTUÉE : $deleted séance(s) orpheline(s) supprimée(s) avec succès de la base ! <<<\n\n";
        }
    } catch (\Throwable $e) {
        echo "Erreur lors de la suppression : " . $e->getMessage() . "\n\n";
    }
}

if (isset($_GET['clear']) || isset($_GET['clean_seance'])) {
    $cacheDir = __DIR__ . '/../var/cache';
    $res = deleteDirectory($cacheDir);
    if ($res) {
        echo "SUCCESS: Le cache Symfony (var/cache) a été vidé !\n\n";
    }
}

echo "=== VÉRIFICATION TABLE SEANCE (BDD) ===\n\n";
try {
    $envFile = __DIR__ . '/../.env.local';
    if (!file_exists($envFile)) $envFile = __DIR__ . '/../.env';
    $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';
    
    if (preg_match('/DATABASE_URL="?mysql:\/\/([^:]+):([^@]+)@([^:\/]+)(?::(\d+))?\/([^"?\s]+)/', $envContent, $m)) {
        $pdo = new PDO("mysql:host={$m[3]};port=" . ($m[4] ?: '3306') . ";dbname={$m[5]};charset=utf8mb4", $m[1], $m[2], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $count = $pdo->query("SELECT COUNT(*) FROM seance")->fetchColumn();
        echo "Nombre d'enregistrements restants dans 'seance' : $count\n\n";
        
        if ($count > 0) {
            $rows = $pdo->query("SELECT * FROM seance")->fetchAll(PDO::FETCH_ASSOC);
            print_r($rows);
        } else {
            echo "La table 'seance' est maintenant propre (0 séance orpheline).\n";
        }
    }
} catch (\Throwable $e) {
    echo "Erreur BDD : " . $e->getMessage() . "\n";
}
