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

function getDbPdo() {
    $envFile = __DIR__ . '/../.env.local';
    if (!file_exists($envFile)) $envFile = __DIR__ . '/../.env';
    $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';
    
    if (preg_match('/^DATABASE_URL=["\']?([^"\']+)["\']?/m', $envContent, $m)) {
        $parsed = parse_url($m[1]);
        $user = $parsed['user'] ?? 'root';
        $pass = $parsed['pass'] ?? '';
        $host = $parsed['host'] ?? '127.0.0.1';
        $port = $parsed['port'] ?? 3306;
        $dbName = ltrim($parsed['path'] ?? 'metamorphysis', '/');
        if (strpos($dbName, '?') !== false) {
            $dbName = substr($dbName, 0, strpos($dbName, '?'));
        }

        return new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
    return null;
}

// Mise à jour Git sur la production si demandé
if (isset($_GET['git_pull'])) {
    echo "=== EXÉCUTION GIT PULL SUR LE SERVEUR ===\n";
    $output = shell_exec('git pull origin main 2>&1');
    echo $output . "\n\n";
}

// Mise à jour de la base de données sur la production si demandé
if (isset($_GET['update_db'])) {
    echo "=== MISE À JOUR BASE DE DONNÉES (DIRECT PDO) ===\n";
    try {
        if ($pdo = getDbPdo()) {

            // 1. Table groupe
            $pdo->exec("CREATE TABLE IF NOT EXISTS `groupe` (
              `id` int NOT NULL AUTO_INCREMENT,
              `nom` varchar(255) NOT NULL,
              `date_creation` datetime NOT NULL,
              `statut` varchar(50) NOT NULL,
              `description` longtext,
              `prestation_id` int NOT NULL,
              PRIMARY KEY (`id`),
              KEY `IDX_4B98C219E45C554` (`prestation_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "✓ Table 'groupe' prête.\n";

            // 2. Table session_groupe
            $pdo->exec("CREATE TABLE IF NOT EXISTS `session_groupe` (
              `id` int NOT NULL AUTO_INCREMENT,
              `date_debut` datetime DEFAULT NULL,
              `date_fin` datetime DEFAULT NULL,
              `statut` varchar(50) NOT NULL,
              `lien_visio` varchar(255) DEFAULT NULL,
              `date_creation` datetime NOT NULL,
              `prestation_id` int NOT NULL,
              `numero_seance` int DEFAULT NULL,
              `titre` varchar(255) DEFAULT NULL,
              `notes_therapeute` longtext,
              `groupe_id` int DEFAULT NULL,
              `est_visible_public` tinyint NOT NULL DEFAULT '1',
              `est_date_adefinir` tinyint(1) NOT NULL DEFAULT '0',
              `message_information` longtext,
              PRIMARY KEY (`id`),
              KEY `IDX_6BD286789E45C554` (`prestation_id`),
              KEY `IDX_6BD286787A45358C` (`groupe_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "✓ Table 'session_groupe' prête.\n";

            // 3. Table inscription_groupe
            $pdo->exec("CREATE TABLE IF NOT EXISTS `inscription_groupe` (
              `id` int NOT NULL AUTO_INCREMENT,
              `nom` varchar(100) NOT NULL,
              `prenom` varchar(100) NOT NULL,
              `email` varchar(180) NOT NULL,
              `telephone` varchar(20) DEFAULT NULL,
              `stripe_customer_id` varchar(255) DEFAULT NULL,
              `stripe_payment_method_id` varchar(255) DEFAULT NULL,
              `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
              `statut_paiement` varchar(50) NOT NULL,
              `montant` double NOT NULL,
              `date_inscription` datetime NOT NULL,
              `session_groupe_id` int NOT NULL,
              `user_id` int DEFAULT NULL,
              `statut_presence` varchar(50) NOT NULL,
              `message_participant` longtext,
              `date_reponse` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `IDX_C86F85839A77086` (`session_groupe_id`),
              KEY `IDX_C86F858A76ED395` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            echo "✓ Table 'inscription_groupe' prête.\n";

            // 4. Colonnes sur prestation
            $colsPrestation = [
                'est_collectif' => 'TINYINT(1) DEFAULT 0 NOT NULL',
                'seuil_minimum' => 'INT DEFAULT NULL',
                'capacite_maximale' => 'INT DEFAULT NULL',
                'delai_limite_heures' => 'INT DEFAULT NULL',
                'recurrence' => 'VARCHAR(100) DEFAULT NULL',
                'label_collectif' => 'VARCHAR(100) DEFAULT NULL',
                'message_date_adefinir' => 'LONGTEXT DEFAULT NULL',
            ];
            foreach ($colsPrestation as $col => $def) {
                $check = $pdo->prepare("SHOW COLUMNS FROM `prestation` LIKE ?");
                $check->execute([$col]);
                if ($check->rowCount() === 0) {
                    $pdo->exec("ALTER TABLE `prestation` ADD `$col` $def");
                    echo "✓ Colonne 'prestation.$col' ajoutée.\n";
                } else {
                    echo "✓ Colonne 'prestation.$col' déjà existante.\n";
                }
            }

            // 5. Colonnes et contraintes sur session_groupe
            $colsSession = [
                'est_date_adefinir' => 'TINYINT(1) DEFAULT 0 NOT NULL',
                'message_information' => 'LONGTEXT DEFAULT NULL',
            ];
            foreach ($colsSession as $col => $def) {
                $check = $pdo->prepare("SHOW COLUMNS FROM `session_groupe` LIKE ?");
                $check->execute([$col]);
                if ($check->rowCount() === 0) {
                    $pdo->exec("ALTER TABLE `session_groupe` ADD `$col` $def");
                    echo "✓ Colonne 'session_groupe.$col' ajoutée.\n";
                }
            }
            $pdo->exec("ALTER TABLE `session_groupe` MODIFY `date_debut` DATETIME DEFAULT NULL");

            // 6. Synchronisation de la prestation "Accompagnement en Groupe"
            $stmt = $pdo->query("SELECT id FROM prestation WHERE slug = 'accompagnement-en-groupe' OR nom LIKE '%Accompagnement en Groupe%' LIMIT 1");
            $prestId = $stmt->fetchColumn();
            if ($prestId) {
                $pdo->exec("UPDATE prestation SET 
                    est_collectif = 1,
                    label_collectif = IFNULL(label_collectif, 'ACCOMPAGNEMENT EN GROUPE'),
                    seuil_minimum = IFNULL(seuil_minimum, 5),
                    capacite_maximale = IFNULL(capacite_maximale, 8),
                    prix = IFNULL(prix, 30)
                WHERE id = $prestId");
                echo "✓ Prestation collective n°$prestId configurée (seuil 5, max 8, 30€).\n";

                // Vérifier s'il y a une session n°1
                $sessCount = $pdo->query("SELECT COUNT(*) FROM session_groupe WHERE prestation_id = $prestId AND est_visible_public = 1")->fetchColumn();
                if ($sessCount == 0) {
                    $pdo->exec("INSERT INTO session_groupe (prestation_id, numero_seance, titre, statut, est_visible_public, est_date_adefinir, date_creation) 
                                VALUES ($prestId, 1, 'Accompagnement en Groupe', 'En cours d\'inscriptions', 1, 1, NOW())");
                    echo "✓ Première séance collective créée avec succès ('Date à définir ultérieurement').\n";
                } else {
                    echo "✓ Séance collective publique déjà active en base.\n";
                }
            }

            echo "\n>>> BASE DE DONNÉES ENTIÈREMENT SYNCHRONISÉE AVEC SUCCÈS ! <<<\n\n";
        }
    } catch (\Throwable $e) {
        echo "Erreur BDD : " . $e->getMessage() . "\n\n";
    }
}

// Nettoyage de la séance orpheline si demandé
if (isset($_GET['clean_seance'])) {
    try {
        if ($pdo = getDbPdo()) {
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
    if ($pdo = getDbPdo()) {
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

