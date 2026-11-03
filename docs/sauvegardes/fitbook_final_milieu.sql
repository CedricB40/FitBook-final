-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: localhost    Database: fitbook_final
-- ------------------------------------------------------
-- Server version	8.4.7

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
-- Table structure for table `activites`
--

DROP TABLE IF EXISTS `activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activites` (
  `id_activite` int NOT NULL AUTO_INCREMENT,
  `nom_activite` varchar(255) NOT NULL,
  `description_activite` longtext NOT NULL,
  PRIMARY KEY (`id_activite`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activites`
--

LOCK TABLES `activites` WRITE;
/*!40000 ALTER TABLE `activites` DISABLE KEYS */;
INSERT INTO `activites` VALUES (2,'Yoga','Posture, respiration et souplesse pour tous les niveaux.'),(3,'Musculation','Travail de la force avec poids libres et machines.'),(4,'Boxe','Cardio et technique de frappe, en cours collectif ou individuel.'),(5,'Pilates','Renforcement profond et gainage en douceur.'),(6,'Cross-training','Circuits intenses qui mêlent force et endurance.');
/*!40000 ALTER TABLE `activites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coachs`
--

DROP TABLE IF EXISTS `coachs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coachs` (
  `id_coach` int NOT NULL AUTO_INCREMENT,
  `biographie_coach` longtext,
  `specialite_coach` varchar(255) NOT NULL,
  `photo_coach` varchar(255) DEFAULT NULL,
  `id_utilisateur` int NOT NULL,
  PRIMARY KEY (`id_coach`),
  UNIQUE KEY `UNIQ_89E318FD50EAE44` (`id_utilisateur`),
  CONSTRAINT `FK_89E318FD50EAE44` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coachs`
--

LOCK TABLES `coachs` WRITE;
/*!40000 ALTER TABLE `coachs` DISABLE KEYS */;
INSERT INTO `coachs` VALUES (1,'Professeure de yoga certifiée, huit ans d\'expérience en cours collectifs et individuels.','Yoga et pilates',NULL,7),(2,'Ancien athlète de force, il accompagne les débutants comme les confirmés en musculation.','Musculation et cross-training',NULL,8),(3,'Boxeur amateur reconverti en coach, il enseigne la technique et le cardio.','Boxe',NULL,9),(4,'Coach de pilates et de gainage, spécialisée dans la posture et la souplesse.','Pilates',NULL,10);
/*!40000 ALTER TABLE `coachs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cours`
--

DROP TABLE IF EXISTS `cours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cours` (
  `id_cours` int NOT NULL AUTO_INCREMENT,
  `titre_cours` varchar(100) NOT NULL,
  `description_cours` longtext,
  `duree_cours` int NOT NULL,
  `capacite_cours` int NOT NULL,
  `id_salle` int NOT NULL,
  `id_activite` int NOT NULL,
  PRIMARY KEY (`id_cours`),
  KEY `IDX_FDCA8C9CA0123F6C` (`id_salle`),
  KEY `IDX_FDCA8C9CE8AEB980` (`id_activite`),
  CONSTRAINT `FK_FDCA8C9CA0123F6C` FOREIGN KEY (`id_salle`) REFERENCES `salles` (`id_salle`),
  CONSTRAINT `FK_FDCA8C9CE8AEB980` FOREIGN KEY (`id_activite`) REFERENCES `activites` (`id_activite`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cours`
--

LOCK TABLES `cours` WRITE;
/*!40000 ALTER TABLE `cours` DISABLE KEYS */;
INSERT INTO `cours` VALUES (1,'Yoga Doux','Postures lentes et respiration, accessible à tous les niveaux.',60,15,1,2),(2,'Yoga Vinyasa','Enchaînements dynamiques synchronisés avec le souffle.',75,12,2,2),(3,'Musculation Initiation','Apprentissage des mouvements de base et des bonnes postures.',60,10,1,3),(4,'Boxe Débutant','Garde, déplacements et premières combinaisons.',60,14,2,4),(5,'Boxe Cardio','Séance intense de boxe sans contact pour brûler des calories.',45,16,3,4),(6,'Pilates Tonic','Renforcement profond et gainage au rythme soutenu.',55,12,3,5),(7,'Cross-training WOD','Circuit du jour mêlant force, endurance et agilité.',50,18,1,6),(8,'Pilates Matinal','Réveil musculaire en douceur pour bien démarrer la journée.',50,10,2,5);
/*!40000 ALTER TABLE `cours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('App\\Migrations\\Version20261101144555','2026-11-01 14:47:35',35),('App\\Migrations\\Version20261101161250','2026-11-01 16:13:59',28),('App\\Migrations\\Version20261101164140','2026-11-01 16:42:33',113),('App\\Migrations\\Version20261101164959','2026-11-01 16:50:45',119),('App\\Migrations\\Version20261101165710','2026-11-01 16:58:09',190),('App\\Migrations\\Version20261101170019','2026-11-01 17:00:44',208),('App\\Migrations\\Version20261101171849','2026-11-01 17:19:23',166),('App\\Migrations\\Version20261102134716','2026-11-02 13:47:44',214);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id_reservation` int NOT NULL AUTO_INCREMENT,
  `date_reservation` datetime NOT NULL,
  `statut_reservation` varchar(100) NOT NULL,
  `id_utilisateur` int NOT NULL,
  `id_seance` int NOT NULL,
  PRIMARY KEY (`id_reservation`),
  UNIQUE KEY `UNIQ_RESERVATIONS_SPORTIF_SEANCE` (`id_utilisateur`,`id_seance`),
  KEY `IDX_4DA23950EAE44` (`id_utilisateur`),
  KEY `IDX_4DA239F94A48E3` (`id_seance`),
  CONSTRAINT `FK_4DA23950EAE44` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE,
  CONSTRAINT `FK_4DA239F94A48E3` FOREIGN KEY (`id_seance`) REFERENCES `seances` (`id_seance`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,'2026-11-02 13:54:40','CONFIRMEE',11,2),(2,'2026-11-02 13:56:53','CONFIRMEE',11,1);
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salles`
--

DROP TABLE IF EXISTS `salles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salles` (
  `id_salle` int NOT NULL AUTO_INCREMENT,
  `nom_salle` varchar(100) NOT NULL,
  `adresse_salle` varchar(255) NOT NULL,
  `ville_salle` varchar(50) NOT NULL,
  `description_salle` longtext,
  `telephone_salle` varchar(255) DEFAULT NULL,
  `photo_salle` varchar(255) DEFAULT NULL,
  `id_utilisateur` int NOT NULL,
  PRIMARY KEY (`id_salle`),
  UNIQUE KEY `UNIQ_799D45AA50EAE44` (`id_utilisateur`),
  CONSTRAINT `FK_799D45AA50EAE44` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salles`
--

LOCK TABLES `salles` WRITE;
/*!40000 ALTER TABLE `salles` DISABLE KEYS */;
INSERT INTO `salles` VALUES (1,'FitZone Jette','Rue de la Forme 12','Jette','Grande salle polyvalente avec espace musculation et studio de cours collectifs.','02 555 01 01',NULL,4),(2,'Studio Équilibre','Avenue du Bien-être 45','Ixelles','Studio lumineux dédié au yoga, au pilates et aux sports de combat.','02 555 02 02',NULL,5),(3,'Boxing Club Nord','Boulevard des Sports 8','Schaerbeek','Club de boxe et de cardio avec ring, sacs et espace de pilates.','02 555 03 03',NULL,6);
/*!40000 ALTER TABLE `salles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seances`
--

DROP TABLE IF EXISTS `seances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seances` (
  `id_seance` int NOT NULL AUTO_INCREMENT,
  `date_heure_debut` datetime NOT NULL,
  `date_heure_fin` datetime NOT NULL,
  `id_cours` int NOT NULL,
  `id_coach` int NOT NULL,
  PRIMARY KEY (`id_seance`),
  KEY `IDX_FC699FF1134FCDAC` (`id_cours`),
  KEY `IDX_FC699FF1D1DC2CFC` (`id_coach`),
  CONSTRAINT `FK_FC699FF1134FCDAC` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`),
  CONSTRAINT `FK_FC699FF1D1DC2CFC` FOREIGN KEY (`id_coach`) REFERENCES `coachs` (`id_coach`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seances`
--

LOCK TABLES `seances` WRITE;
/*!40000 ALTER TABLE `seances` DISABLE KEYS */;
INSERT INTO `seances` VALUES (1,'2026-11-03 18:00:00','2026-11-03 19:00:00',1,1),(2,'2026-11-03 19:30:00','2026-11-03 20:30:00',4,3),(3,'2026-11-04 07:30:00','2026-11-04 08:20:00',8,4),(4,'2026-11-04 12:30:00','2026-11-04 13:30:00',3,2),(5,'2026-11-04 18:30:00','2026-11-04 19:15:00',5,3),(6,'2026-11-05 18:00:00','2026-11-05 19:15:00',2,1),(7,'2026-11-05 19:00:00','2026-11-05 19:50:00',7,2),(8,'2026-11-06 09:00:00','2026-11-06 09:55:00',6,4),(9,'2026-11-06 17:30:00','2026-11-06 18:30:00',1,1),(10,'2026-11-07 18:00:00','2026-11-07 19:00:00',4,3),(11,'2026-11-08 10:00:00','2026-11-08 10:50:00',7,2),(12,'2026-11-09 18:30:00','2026-11-09 19:30:00',3,2),(13,'2026-11-10 08:00:00','2026-11-10 08:50:00',8,4),(14,'2026-11-11 19:00:00','2026-11-11 20:15:00',2,1);
/*!40000 ALTER TABLE `seances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom_utilisateur` varchar(50) NOT NULL,
  `prenom_utilisateur` varchar(50) NOT NULL,
  `email_utilisateur` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role_utilisateur` varchar(50) NOT NULL,
  `email_confirme` tinyint NOT NULL DEFAULT '0',
  `date_inscription` date NOT NULL,
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `UNIQ_UTILISATEURS_EMAIL` (`email_utilisateur`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'Dupont','Marie','marie@test.be','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_SPORTIF',0,'2026-11-01'),(2,'Admin','FitBook','admin@fitbook.be','$2y$13$myul25oi7rwrEh4VStmgO.4fMv/cgeyIG/q79IOrvfvtoFMMqSv4O','ROLE_ADMIN',1,'2026-11-01'),(3,'test','test','test@test.be','$2y$13$fG/4ht9tejJLQwQnH9dXEuTYarhmH.PV1041mK1bTQ28ZUZTTRfXO','ROLE_SPORTIF',0,'2026-11-02'),(4,'Lambert','Sophie','gestionnaire1@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_GESTIONNAIRE',1,'2026-11-02'),(5,'Dupuis','Karim','gestionnaire2@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_GESTIONNAIRE',1,'2026-11-02'),(6,'Martens','Julie','gestionnaire3@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_GESTIONNAIRE',1,'2026-11-02'),(7,'Moreau','Nadia','coach1@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_COACH',1,'2026-11-02'),(8,'Peeters','Thomas','coach2@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_COACH',1,'2026-11-02'),(9,'Benali','Yassine','coach3@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_COACH',1,'2026-11-02'),(10,'Janssens','Laura','coach4@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_COACH',1,'2026-11-02'),(11,'Rossi','Marco','sportif1@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_SPORTIF',1,'2026-11-02'),(12,'Claes','Emma','sportif2@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_SPORTIF',1,'2026-11-02');
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'fitbook_final'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-02 15:00:56
