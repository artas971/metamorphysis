-- MySQL dump 10.13  Distrib 8.0.31, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: metamorphysis
-- ------------------------------------------------------
-- Server version	8.0.31

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20260522155058','2026-05-22 15:51:06',23),('DoctrineMigrations\\Version20260522160404','2026-05-22 16:04:24',23),('DoctrineMigrations\\Version20260609085606','2026-06-09 12:43:36',293),('DoctrineMigrations\\Version20260609125205','2026-06-09 12:52:12',90),('DoctrineMigrations\\Version20260609125316','2026-06-09 12:53:21',49),('DoctrineMigrations\\Version20260610133019','2026-06-10 14:12:00',173),('DoctrineMigrations\\Version20260612215800','2026-06-12 21:58:18',144),('DoctrineMigrations\\Version20260612223549','2026-06-12 22:36:02',116),('DoctrineMigrations\\Version20260612224400','2026-06-12 22:44:04',110),('DoctrineMigrations\\Version20260612225311','2026-06-12 22:53:17',116),('DoctrineMigrations\\Version20260612230605','2026-06-12 23:06:11',264),('DoctrineMigrations\\Version20260612232425','2026-06-12 23:24:32',88),('DoctrineMigrations\\Version20260612233238','2026-06-12 23:32:44',92),('DoctrineMigrations\\Version20260613202452','2026-06-13 20:26:30',553),('DoctrineMigrations\\Version20260613204849','2026-06-13 20:48:58',161),('DoctrineMigrations\\Version20260614110537','2026-06-14 11:06:30',132),('DoctrineMigrations\\Version20260614112713','2026-06-14 11:27:33',103),('DoctrineMigrations\\Version20260615214536',NULL,NULL),('DoctrineMigrations\\Version20260619172345','2026-06-19 17:38:41',148),('DoctrineMigrations\\Version20260620142121','2026-06-20 14:40:22',187),('DoctrineMigrations\\Version20260620150051','2026-06-20 15:00:57',182),('DoctrineMigrations\\Version20260621185522',NULL,NULL),('DoctrineMigrations\\Version20260626163632','2026-06-26 16:38:23',143),('DoctrineMigrations\\Version20260626172631','2026-06-26 17:27:37',100),('DoctrineMigrations\\Version20260626173106','2026-06-26 17:31:14',117),('DoctrineMigrations\\Version20260627193451','2026-06-27 19:36:07',166),('DoctrineMigrations\\Version20260627232456','2026-06-27 23:35:32',159),('DoctrineMigrations\\Version20260628191342','2026-06-28 19:13:50',177),('DoctrineMigrations\\Version20260628205013','2026-06-28 20:50:54',185),('DoctrineMigrations\\Version20260702164722','2026-07-02 16:47:33',200),('DoctrineMigrations\\Version20260702192143','2026-07-02 19:22:07',223),('DoctrineMigrations\\Version20260703165337','2026-07-03 16:53:39',264),('DoctrineMigrations\\Version20260708100614','2026-07-08 10:06:22',272),('DoctrineMigrations\\Version20260709201452','2026-07-09 20:16:10',382),('DoctrineMigrations\\Version20260709225416','2026-07-09 22:54:18',292),('DoctrineMigrations\\Version20260710144614','2026-07-10 14:46:45',237),('DoctrineMigrations\\Version20260710183734','2026-07-10 18:38:30',303),('DoctrineMigrations\\Version20260711201010','2026-07-11 20:13:01',327),('DoctrineMigrations\\Version20260711204036','2026-07-11 20:40:44',205),('DoctrineMigrations\\Version20260711211729','2026-07-11 21:18:08',274),('DoctrineMigrations\\Version20260713144443','2026-07-13 14:44:51',394),('DoctrineMigrations\\Version20260713150142','2026-07-13 15:01:49',237),('DoctrineMigrations\\Version20260723121921','2026-07-23 12:19:25',286),('DoctrineMigrations\\Version20260726151058','2026-07-26 15:11:00',427);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `etape`
--

DROP TABLE IF EXISTS `etape`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etape` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `texte` longtext NOT NULL,
  `icone` varchar(255) NOT NULL,
  `section_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_285F75DDD823E37A` (`section_id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `etape`
--

LOCK TABLES `etape` WRITE;
/*!40000 ALTER TABLE `etape` DISABLE KEYS */;
INSERT INTO `etape` VALUES (31,'CETTE DÉMARCHE PEUT VOUS CORRESPONDRE SI...','· Vous ressentez que les mêmes situations se répètent dans votre vie.\n· Vous avez conscience d\'un problème récurrent que vous n\'arrivez pas à résoudre.\n→ Vous souhaitez comprendre vos schémas relationnels et émotionnels.\n· Vous êtes prêt(e) à un travail de fond et à vous remettre en question.\n→ Vous aspirez à une transformation profonde et durable.','',51),(30,'S\'ALIGNER','Vivre en cohérence\navec vos valeurs,\nvos besoins\net vos désirs profonds.','bi-crosshair',48),(29,'TRANSFORMER','Reprogrammer\nde nouveaux choix,\nalignés avec\nqui vous êtes vraiment.','libellule.svg',48),(28,'CONFRONTER','Accueillir la vérité,\nsortir des mécanismes\nde protection\net d\'évitement.','bi-symmetry-vertical',48),(40,'S\'ALIGNER','Retrouver une cohérence intérieure et avancer avec plus de liberté et de sens.','bi-brightness-high',44),(26,'OBSERVER','Prendre conscience\nde ses schémas\net fonctionnements\nrépétitifs.','bi-eye',48),(27,'COMPRENDRE','Identifier les origines,\nles blessures et les\nmessages cachés\nderrière les comportements.','bi-brain',48),(37,'OBSERVER','Prendre conscience de ce qui se répète et de ses impacts.','bi-eye',44),(38,'COMPRENDRE','Mettre en lumière les mécanismes inconscients qui influencent vos choix.','bi-brain',44),(32,'CETTE DÉMARCHE NE VOUS CONVIENDRA PROBABLEMENT PAS SI...','· Vous cherchez des solutions rapides ou toutes faites.\n→ Vous attendez que quelqu\'un d\'autre change à votre place.\n· Vous n\'êtes pas prêt(e) à regarder ce qui gêne.\n→ Vous refusez toute remise en question.\n→ Vous préférez rester dans vos certitudes.','',51),(39,'TRANSFORMER','Construire de nouvelles réponses et sortir des schémas limitants.','bi-flower1',44);
/*!40000 ALTER TABLE `etape` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horaire_hebdomadaire`
--

DROP TABLE IF EXISTS `horaire_hebdomadaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horaire_hebdomadaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jour` int NOT NULL,
  `est_ouvert` tinyint NOT NULL,
  `ouverture_matin` time DEFAULT NULL,
  `fermeture_matin` time DEFAULT NULL,
  `ouverture_apres_midi` time DEFAULT NULL,
  `fermeture_apres_midi` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horaire_hebdomadaire`
--

LOCK TABLES `horaire_hebdomadaire` WRITE;
/*!40000 ALTER TABLE `horaire_hebdomadaire` DISABLE KEYS */;
INSERT INTO `horaire_hebdomadaire` VALUES (16,1,1,'09:00:00','11:00:00','14:00:00','18:00:00'),(17,2,1,'09:00:00','11:00:00','14:00:00','18:00:00'),(18,3,1,'09:00:00','11:00:00','14:00:00','18:00:00'),(19,4,1,'09:00:00','11:00:00','14:00:00','18:00:00'),(20,5,1,'09:00:00','11:00:00','14:00:00','18:00:00'),(21,6,0,NULL,NULL,NULL,NULL),(22,7,0,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `horaire_hebdomadaire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indisponibilite`
--

DROP TABLE IF EXISTS `indisponibilite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indisponibilite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `debut` datetime NOT NULL,
  `fin` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indisponibilite`
--

LOCK TABLES `indisponibilite` WRITE;
/*!40000 ALTER TABLE `indisponibilite` DISABLE KEYS */;
/*!40000 ALTER TABLE `indisponibilite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page`
--

DROP TABLE IF EXISTS `page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `afficher_menu` tinyint NOT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `is_published` tinyint NOT NULL,
  `afficher_titre` tinyint NOT NULL DEFAULT '1',
  `fond_blocs_unifie` varchar(50) DEFAULT NULL,
  `ordre_menu` int DEFAULT '0',
  `afficher_footer` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page`
--

LOCK TABLES `page` WRITE;
/*!40000 ALTER TABLE `page` DISABLE KEYS */;
INSERT INTO `page` VALUES (23,'À vous','a-qui-sadresse-cette-demarche',1,NULL,1,0,'pourpre',3,0),(22,'À Propos','a-propos',1,NULL,1,0,'pourpre',2,0),(21,'L\'Expérience','lexperience',1,NULL,1,0,'olive',1,0),(20,'Accueil','accueil',1,NULL,1,0,'pourpre',0,0),(24,'Prestations','prestations',1,NULL,1,1,'pourpre',4,0),(25,'Mentions Légales & Confidentialité','mentions-legales',0,NULL,1,1,'individuel',0,1);
/*!40000 ALTER TABLE `page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prestation`
--

DROP TABLE IF EXISTS `prestation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prestation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `prix` double NOT NULL,
  `duree` int DEFAULT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `est_mis_en_avant` tinyint NOT NULL,
  `icone` varchar(255) DEFAULT NULL,
  `unite_prix` varchar(50) DEFAULT NULL,
  `ordre` int DEFAULT NULL,
  `description_complementaire` longtext,
  `lien_video` varchar(255) DEFAULT NULL,
  `nombre_seances` int NOT NULL,
  `prix_affiche` varchar(255) DEFAULT NULL,
  `min_personnes` int NOT NULL DEFAULT '1',
  `max_personnes` int NOT NULL DEFAULT '1',
  `prix_couple` double DEFAULT NULL,
  `prix_groupe` double DEFAULT NULL,
  `tarifs_par_personne` json DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestation`
--

LOCK TABLES `prestation` WRITE;
/*!40000 ALTER TABLE `prestation` DISABLE KEYS */;
INSERT INTO `prestation` VALUES (10,'Séance Individuelle (Analyse Transactionnelle)','Exploration en profondeur d\'une problématique ciblée, mise en lumière des états du moi et déconstruction des scénarios répétitifs.',70,60,NULL,NULL,0,'bi-person',NULL,2,NULL,NULL,1,'70 € / SÉANCE',1,1,NULL,NULL,'{\"1\": {\"prix\": 70, \"titre\": \"1 personne\", \"sousTitre\": \"Individuel\"}}','seance-individuelle-analyse-transactionnelle'),(9,'Consultation Initiale','Un premier diagnostic complet pour cibler vos besoins, explorer vos attentes et définir ensemble une stratégie d\'évolution et d\'accompagnement sur-mesure.',50,45,NULL,NULL,0,'bi-person',NULL,1,NULL,NULL,1,'45 MIN . 50 €',1,1,NULL,NULL,'{\"1\": {\"prix\": 50, \"titre\": \"1 personne\", \"sousTitre\": \"Individuel\"}}','consultation-initiale'),(11,'COUPLE ET RELATIONS','Comprendre les dynamiques relationnelles',80,60,NULL,NULL,0,'bi-diagram-3','SÉANCE',3,'Comprendre les dynamiques relationnelles et sortir des schémas répétitifs.',NULL,1,'80 À 120 € / SÉANCE',2,3,80,120,'{\"2\": {\"prix\": 80, \"titre\": \"2 personnes\", \"sousTitre\": \"Couple\"}, \"3\": {\"prix\": 120, \"titre\": \"3 personnes et +\", \"sousTitre\": \"Autre\"}}','couple-et-relations'),(12,'Famille','Retrouver un dialogue plus serein',80,60,NULL,NULL,0,'bi-house-heart',NULL,4,'Retrouver un dialogue plus serein et comprendre les mécanismes qui influencent les relations familiales.',NULL,1,'80 À 120 € / SÉANCE',2,3,80,120,'{\"2\": {\"prix\": 80, \"titre\": \"2 personnes\", \"sousTitre\": \"familles\"}, \"3\": {\"prix\": 120, \"titre\": \"3 personnes\", \"sousTitre\": \"familles\"}}','famille'),(13,'THÉRAPIE DE GROUPE','Explorer, partager et avancer ensemble',130,180,NULL,NULL,0,'bi-diagram-3',NULL,5,'Explorer, partager et avancer ensemble dans un cadre sécurisant',NULL,3,'À partir de 30€',5,4,NULL,NULL,'[]','therapie-de-groupe');
/*!40000 ALTER TABLE `prestation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_rendez_vous` datetime NOT NULL,
  `statut` varchar(255) NOT NULL,
  `user_id` int NOT NULL,
  `prestation_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42C84955A76ED395` (`user_id`),
  KEY `IDX_42C849559E45C554` (`prestation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation`
--

LOCK TABLES `reservation` WRITE;
/*!40000 ALTER TABLE `reservation` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reset_password_request`
--

DROP TABLE IF EXISTS `reset_password_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reset_password_request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `selector` varchar(20) NOT NULL,
  `hashed_token` varchar(100) NOT NULL,
  `requested_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7CE748AA76ED395` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reset_password_request`
--

LOCK TABLES `reset_password_request` WRITE;
/*!40000 ALTER TABLE `reset_password_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `reset_password_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seance`
--

DROP TABLE IF EXISTS `seance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero` int NOT NULL,
  `duree` int NOT NULL,
  `date_rendez_vous` datetime DEFAULT NULL,
  `statut` varchar(255) NOT NULL,
  `prestation_id` int NOT NULL,
  `user_id` int NOT NULL,
  `lien_visio` varchar(255) DEFAULT NULL,
  `date_creation` datetime NOT NULL,
  `nombre_personnes` int DEFAULT NULL,
  `montant_paye` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DF7DFD0E9E45C554` (`prestation_id`),
  KEY `IDX_DF7DFD0EA76ED395` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seance`
--

LOCK TABLES `seance` WRITE;
/*!40000 ALTER TABLE `seance` DISABLE KEYS */;
INSERT INTO `seance` VALUES (53,1,60,'2026-09-03 09:00:00','Confirmé',10,8,'https://metamorphysis.daily.co/MKhilMJLjEqDfWlDN8Tr','2026-09-02 13:25:40',1,70),(54,1,45,'2026-08-31 13:38:15','Effectuée',9,9,NULL,'2026-09-02 13:38:15',1,NULL),(56,1,45,'2026-09-05 13:41:58','Annulé',9,10,NULL,'2026-09-02 13:41:58',1,NULL);
/*!40000 ALTER TABLE `seance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `section`
--

DROP TABLE IF EXISTS `section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `section` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ordre` int NOT NULL,
  `disposition` varchar(255) NOT NULL,
  `media` varchar(255) DEFAULT NULL,
  `page_id` int NOT NULL,
  `largeur_media` int DEFAULT NULL,
  `hauteur_media` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `contenu` longtext,
  `titre` varchar(255) DEFAULT NULL,
  `balise_html` varchar(255) DEFAULT NULL,
  `couleur_fond` varchar(50) DEFAULT 'plum',
  `citation` longtext,
  `citation_pos_x` int DEFAULT NULL,
  `citation_pos_y` int DEFAULT NULL,
  `citation_largeur` int DEFAULT NULL,
  `citation_couleur_fond` varchar(50) DEFAULT NULL,
  `citation_couleur_texte` varchar(50) DEFAULT NULL,
  `image_pos_x` int DEFAULT NULL,
  `image_pos_y` int DEFAULT NULL,
  `citation_hauteur_max` int DEFAULT NULL,
  `bouton_texte` varchar(255) DEFAULT NULL,
  `bouton_lien` varchar(255) DEFAULT NULL,
  `bouton_style` varchar(50) DEFAULT 'gold',
  `bouton_cible` varchar(20) DEFAULT '_self',
  `image_superposition` varchar(50) DEFAULT 'standard',
  `image_zindex` int DEFAULT '1',
  `crop_haut` int DEFAULT '0',
  `crop_bas` int DEFAULT '0',
  `crop_gauche` int DEFAULT '0',
  `crop_droite` int DEFAULT '0',
  `image_lien` varchar(255) DEFAULT NULL,
  `decalage_pos_y` int DEFAULT '0',
  `image_cadre` tinyint NOT NULL DEFAULT '1',
  `texte_gras` tinyint NOT NULL DEFAULT '0',
  `image_cadre_couleur` varchar(50) DEFAULT 'plum',
  `sous_titre` longtext,
  `titre_couleur` varchar(50) DEFAULT 'gold-hover',
  `sous_titre_couleur` varchar(50) DEFAULT 'ivory',
  `texte_couleur` varchar(50) DEFAULT 'ivory',
  `image_cadre_haut` int DEFAULT '0',
  `image_cadre_bas` int DEFAULT '0',
  `image_cadre_gauche` int DEFAULT '0',
  `image_cadre_droite` int DEFAULT '0',
  `titre_ligne_decor` varchar(50) DEFAULT 'apres',
  `padding_haut` int DEFAULT '48',
  `padding_bas` int DEFAULT '48',
  `padding_gauche` int DEFAULT '48',
  `padding_droite` int DEFAULT '48',
  `marge_haut` int DEFAULT '0',
  `marge_bas` int DEFAULT '0',
  `alignement_texte` varchar(50) DEFAULT 'center',
  `hauteur_min` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2D737AEFC4663E4` (`page_id`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `section`
--

LOCK TABLES `section` WRITE;
/*!40000 ALTER TABLE `section` DISABLE KEYS */;
INSERT INTO `section` VALUES (55,2,'texte_centre',NULL,25,NULL,NULL,NULL,'<h3>1. Données collectées</h3><p>Dans le cadre de la prise de contact, de la gestion de votre compte et de la réservation de séances d\'accompagnement, METAMORPHYSIS est amenée à collecter des informations strictement nécessaires : nom, prénom, adresse e-mail, numéro de téléphone et historique des réservations.</p><h3>2. Finalité des traitements</h3><p>Vos données personnelles sont traitées exclusivement pour assurer la gestion de vos rendez-vous, le suivi de vos accompagnements personnalisés, la facturation et la communication liée à vos séances. Vos données ne sont en aucun cas cédées, louées ou vendues à des tiers.</p><h3>3. Vos droits</h3><p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d\'un droit d\'accès, de rectification, de suppression et de portabilité de vos données. Vous pouvez exercer ces droits à tout moment en écrivant à : <strong>contact@metamorphysis.fr</strong>.</p>','Politique de Confidentialité','section','olive',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum','Protection de vos données personnelles [RGPD]','gold-hover','ivory','ivory',0,0,0,0,'apres',48,48,48,48,0,0,'center',NULL),(50,1,'img_droite','canape-taupe-6a7f1ba5a707a642826507.jpg',23,NULL,NULL,NULL,NULL,'À QUI S\'ADRESSE CETTE DÉMARCHE','h2','plum',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum',NULL,'gold-hover','ivory','ivory',0,0,0,0,'apres',48,48,48,0,0,0,'center',NULL),(51,2,'grille_colonnes',NULL,23,NULL,NULL,NULL,NULL,NULL,'h2','olive',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum',NULL,'gold-hover','ivory','ivory',0,0,0,0,'apres',48,48,48,48,-80,0,'center',NULL),(52,3,'bandeau_conclusion',NULL,23,NULL,NULL,NULL,'Cette démarche demande du courage.\nMais c\'est aussi là que tout commence.',NULL,'h2','none',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum',NULL,'gold-hover','ivory','ivory',0,0,0,0,'apres',0,0,0,0,0,0,'center',NULL),(53,1,'slider_prestations',NULL,24,NULL,NULL,NULL,NULL,'NOS OFFRES D\'ACCOMPAGNEMENT','section','plum',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum','DES PARCOURS SUR-MESURE ADAPTÉS À VOTRE ÉVOLUTION','gold-hover','ivory','ivory',0,0,0,0,'apres',48,60,48,48,0,0,'center',NULL),(54,1,'texte_centre',NULL,25,NULL,NULL,NULL,'<h3>1. Éditeur du site</h3><p><strong>METAMORPHYSIS</strong><br>Consultante en relation humaine & Analyse transactionnelle<br>Responsable de la publication : <strong>Louisa CHOUIHI</strong><br>SIRET : En cours d\'immatriculation<br>Contact : contact@metamorphysis.fr</p><h3>2. Hébergement</h3><p>Le site est hébergé par la société <strong>Hostinger International Ltd.</strong><br>61 Lordou Vironos Street, 6023 Larnaca, Chypre<br>Site web : <a href=\"https://www.hostinger.fr\" target=\"_blank\" rel=\"noopener noreferrer\" style=\"color: var(--meta-gold);\">www.hostinger.fr</a></p><h3>3. Propriété intellectuelle</h3><p>L\'ensemble des éléments graphiques, textuels, logos, marques et structures composant ce site sont la propriété exclusive de METAMORPHYSIS et de Louisa CHOUIHI. Toute reproduction, diffusion, modification ou utilisation sans autorisation préalable expresse est strictement interdite.</p>','Mentions Légales','section','olive',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum','Éditeur du site & Hébergement','gold-hover','ivory','ivory',0,0,0,0,'apres',48,48,48,48,0,0,'center',NULL),(49,1,'img_droite','louisa-presentation.jpg',22,NULL,NULL,NULL,'Diplômée et passionnée par l\'humain, j\'accompagne depuis plusieurs années les personnes et les professionnels dans la compréhension de leurs dynamiques intérieures et relationnelles.\n\nÀ travers l\'analyse transactionnelle et une écoute bienveillante mais exigeante, nous explorons ensemble ce qui se joue au-delà des apparences pour déconstruire les croyances limitantes et révéler votre véritable potentiel.','CHOUIHI LOUISA','h2','plum','L\'accompagnement Métamorphysis s\'adresse à celles et ceux qui ressentent le besoin d\'éclairer leur trajectoire, de dénouer des blocages profonds et de reprendre les rênes de leur vie avec clarté, authenticité et sérénité.',15,-120,85,'meta-olive','meta-ivory',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum','CONSULTANTE EN RELATION HUMAINE','gold-hover','gold-hover','ivory',0,0,0,0,'apres',48,48,48,0,0,0,'center',NULL),(47,1,'img_droite','vase.jpg',21,NULL,NULL,NULL,'Grâce à l\'analyse transactionnelle, nous mettons en lumière les schémas inconscients qui influencent vos relations, vos choix et votre bien-être.\n\nComprendre. Transformer. Se libérer. Redevenir auteur de sa propre vie.','L\'EXPÉRIENCE','h2','olive',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum','- γνῶθι σεαυτόν - CONNAIS-TOI TOI-MÊME','gold-hover','ivory','ivory',0,0,0,48,'apres',0,0,48,0,0,0,'center',0),(48,2,'grille_colonnes',NULL,21,NULL,NULL,NULL,NULL,'LE PROCESSUS','h2','olive',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum',NULL,'gold-hover','ivory','ivory',0,0,0,0,'apres',48,48,48,48,0,0,'center',NULL),(46,3,'slider_prestations',NULL,20,NULL,NULL,NULL,'Les séances ne sont pas remboursées par la sécurité sociale.','Les espaces d\'accompagnement','section','plum','Le rythme et la durée de l\'accompagnement sont définis ensemble lors de la première rencontre, en fonction de vos besoins et de vos objectifs.',-10,-40,90,'meta-olive','meta-gold',0,0,NULL,'En savoir plus','/prestations','gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum','DIFFÉRENTES MODALITÉS POUR RÉPONDRE À VOS BESOINS','gold-hover','ivory','ivory',0,0,0,0,'apres',48,60,48,48,0,0,'center',NULL),(45,2,'img_droite','fauteuils.jpg',20,NULL,NULL,NULL,'Certaines personnes viennent avec une problématique précise. D\'autres ressentent simplement qu\'un schéma se répète sans parvenir à l\'expliquer.\n\nLors de la première rencontre, nous prenons le temps de comprendre votre situation afin de définir ensemble le rythme et l\'orientation de l\'accompagnement.','Chaque transformation commence par une rencontre.','section','plum',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,'PRENDRE RENDEZ-VOUS','/prestations','btn-gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum','CHAQUE PARCOURS EST UNIQUE','gold-hover','ivory','ivory',0,0,0,35,'apres',48,48,48,48,0,0,'center',NULL),(44,1,'grille_colonnes',NULL,20,NULL,NULL,NULL,'Un processus d\'accompagnement sur-mesure en quatre étapes pour vous libérer des schémas limitants.','VOTRE CHEMINEMENT VERS LA TRANSFORMATION','section','plum',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum','BIENVENUE CHEZ METAMORPHYSIS','gold-hover','ivory','ivory',0,0,0,0,'apres',60,48,48,48,0,0,'center',NULL),(56,3,'texte_centre',NULL,25,NULL,NULL,NULL,'<p>Chaque séance d\'accompagnement fait l\'objet d\'une préparation sur-mesure et d\'un créneau horaire strictement réservé à votre attention.</p><p><strong>Délai de prévenance :</strong> Tout report ou annulation de séance doit être formulé au minimum <strong>48 heures à l\'avance</strong> par e-mail ou depuis votre espace personnel.</p><p>En cas d\'annulation tardive (moins de 48h avant le rendez-vous) ou de non-présentation, la séance sera considérée comme due et ne donnera lieu à aucun remboursement, sauf cas de force majeure dûment justifié.</p>','Conditions d\'Annulation','section','olive',NULL,-10,-40,90,'meta-olive','meta-gold',0,0,NULL,NULL,NULL,'gold','_self','standard',1,0,0,0,0,NULL,0,1,0,'plum','Modalités relatives aux rendez-vous','gold-hover','ivory','ivory',0,0,0,0,'apres',48,48,48,48,0,0,'center',NULL);
/*!40000 ALTER TABLE `section` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `section_prestation`
--

DROP TABLE IF EXISTS `section_prestation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `section_prestation` (
  `section_id` int NOT NULL,
  `prestation_id` int NOT NULL,
  PRIMARY KEY (`section_id`,`prestation_id`),
  KEY `IDX_CC78B572D823E37A` (`section_id`),
  KEY `IDX_CC78B5729E45C554` (`prestation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `section_prestation`
--

LOCK TABLES `section_prestation` WRITE;
/*!40000 ALTER TABLE `section_prestation` DISABLE KEYS */;
INSERT INTO `section_prestation` VALUES (46,9),(46,10),(46,11),(46,12),(46,13),(46,14),(53,9),(53,10),(53,11),(53,12),(53,13),(53,14);
/*!40000 ALTER TABLE `section_prestation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (6,'artas971@gmail.com','[\"ROLE_ADMIN\", \"ROLE_USER\"]','$2y$13$yj744CfO5wQQpb/Da50jae7QnKIDF/tubxntcpVJfno6TGTn21ur.','Admin','Metamorphysis',NULL),(7,'metamorphysisconsulting@gmail.com','[\"ROLE_ADMIN\", \"ROLE_USER\"]','$2y$13$ITswhuwaOJdbDg/keximyuSF0dehreo.xrfTfpZXVTF.KIjIzBMaC','Louisa','Chouihi','0600000000'),(8,'artas971@hotmail.fr','[]','$2y$13$644eBmummfQ3RI/920iW0.InQ82yw2r5x67WC4LJgRcXcG8Ymqgwa','floyd','george','yA4jwF2f3yoHbHnnVaXyv2I5cEtreE1wUGNrZ2VUN01KdEdaR3c9PQ=='),(9,'client.test@gmail.com','[\"ROLE_USER\"]','$2y$13$UnvixU/w0qZolNNBszOxxuE1lnO9UktS9ECE1IV.cyV/oxN6sK8yO','Jean','Dupont','nrjoLBa/XyvunnfwTZM5klBTSHdhNzNkSlNjUGM3T05BZ3hlRWc9PQ=='),(10,'client.annule@gmail.com','[\"ROLE_USER\"]','$2y$13$gFQ/LtczqF9msPsSr67.OucmNVgPhpAyR4RJPLXQLrbtcCd.2vuFe','Sophie','Martin','eR+Gg7Eb/a45xuDYvhAv8Vd4Z1B6QXUvMmlrUGlIazdHQVdPM3c9PQ==');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-02 15:42:30
