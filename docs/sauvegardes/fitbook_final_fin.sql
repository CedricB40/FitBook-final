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
-- Table structure for table `avis`
--

DROP TABLE IF EXISTS `avis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avis` (
  `id_avis` int NOT NULL AUTO_INCREMENT,
  `cible_avis` varchar(50) NOT NULL,
  `note_avis` int NOT NULL,
  `commentaire_avis` longtext,
  `date_avis` date NOT NULL,
  `id_reservation` int NOT NULL,
  PRIMARY KEY (`id_avis`),
  UNIQUE KEY `UNIQ_AVIS_RESERVATION_CIBLE` (`id_reservation`,`cible_avis`),
  KEY `IDX_8F91ABF05ADA84A2` (`id_reservation`),
  CONSTRAINT `FK_8F91ABF05ADA84A2` FOREIGN KEY (`id_reservation`) REFERENCES `reservations` (`id_reservation`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avis`
--

LOCK TABLES `avis` WRITE;
/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
INSERT INTO `avis` VALUES (1,'SALLE',4,'test commentaire','2026-11-02',6),(2,'COACH',3,'test','2026-11-02',5),(3,'SALLE',2,'test','2026-11-02',5);
/*!40000 ALTER TABLE `avis` ENABLE KEYS */;
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
INSERT INTO `coachs` VALUES (1,'Professeure de yoga certifiée, huit ans d\'expérience en cours collectifs et individuels.','Yoga et pilates','/images/coach-nadia.jpg',7),(2,'Ancien athlète de force, il accompagne les débutants comme les confirmés en musculation.','Musculation','/images/coach-thomas.jpg',8),(3,'Boxeur amateur reconverti en coach, il enseigne la technique et le cardio.','Boxe','/images/coach-yassine.jpg',9),(4,'Coach de pilates et de gainage, spécialisée dans la posture et la souplesse.','Pilates','/images/coach-laura.jpg',10);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
-- Table structure for table `disponibilites`
--

DROP TABLE IF EXISTS `disponibilites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disponibilites` (
  `id_disponibilite` int NOT NULL AUTO_INCREMENT,
  `jour_semaine` smallint NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `id_coach` int NOT NULL,
  PRIMARY KEY (`id_disponibilite`),
  KEY `IDX_B0F3489CD1DC2CFC` (`id_coach`),
  CONSTRAINT `FK_B0F3489CD1DC2CFC` FOREIGN KEY (`id_coach`) REFERENCES `coachs` (`id_coach`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disponibilites`
--

LOCK TABLES `disponibilites` WRITE;
/*!40000 ALTER TABLE `disponibilites` DISABLE KEYS */;
INSERT INTO `disponibilites` VALUES (1,1,'09:00:00','12:00:00',1),(2,2,'18:00:00','22:00:00',1),(3,3,'19:00:00','21:00:00',1),(4,4,'18:00:00','21:00:00',1),(5,5,'17:00:00','20:00:00',1),(6,3,'12:00:00','14:00:00',2),(7,4,'18:00:00','21:00:00',2),(8,6,'09:00:00','12:00:00',2),(9,7,'09:00:00','12:00:00',2),(10,2,'18:00:00','21:00:00',3),(11,3,'18:00:00','21:00:00',3),(12,6,'10:00:00','14:00:00',3),(13,1,'08:00:00','12:00:00',4),(14,5,'08:00:00','12:00:00',4);
/*!40000 ALTER TABLE `disponibilites` ENABLE KEYS */;
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
INSERT INTO `doctrine_migration_versions` VALUES ('App\\Migrations\\Version20261101144555','2026-11-01 14:47:35',35),('App\\Migrations\\Version20261101161250','2026-11-01 16:13:59',28),('App\\Migrations\\Version20261101164140','2026-11-01 16:42:33',113),('App\\Migrations\\Version20261101164959','2026-11-01 16:50:45',119),('App\\Migrations\\Version20261101165710','2026-11-01 16:58:09',190),('App\\Migrations\\Version20261101170019','2026-11-01 17:00:44',208),('App\\Migrations\\Version20261101171849','2026-11-01 17:19:23',166),('App\\Migrations\\Version20261102134716','2026-11-02 13:47:44',214),('App\\Migrations\\Version20261102140934','2026-11-02 14:11:42',16),('App\\Migrations\\Version20261102154808','2026-11-02 15:50:38',518),('App\\Migrations\\Version20261102155904','2026-11-02 16:11:17',115),('App\\Migrations\\Version20261102172607','2026-11-02 17:26:17',195);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favoris_coachs`
--

DROP TABLE IF EXISTS `favoris_coachs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favoris_coachs` (
  `id_utilisateur` int NOT NULL,
  `id_coach` int NOT NULL,
  PRIMARY KEY (`id_utilisateur`,`id_coach`),
  KEY `IDX_B3852C4050EAE44` (`id_utilisateur`),
  KEY `IDX_B3852C40D1DC2CFC` (`id_coach`),
  CONSTRAINT `FK_B3852C4050EAE44` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE,
  CONSTRAINT `FK_B3852C40D1DC2CFC` FOREIGN KEY (`id_coach`) REFERENCES `coachs` (`id_coach`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favoris_coachs`
--

LOCK TABLES `favoris_coachs` WRITE;
/*!40000 ALTER TABLE `favoris_coachs` DISABLE KEYS */;
INSERT INTO `favoris_coachs` VALUES (11,4);
/*!40000 ALTER TABLE `favoris_coachs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favoris_cours`
--

DROP TABLE IF EXISTS `favoris_cours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favoris_cours` (
  `id_utilisateur` int NOT NULL,
  `id_cours` int NOT NULL,
  PRIMARY KEY (`id_utilisateur`,`id_cours`),
  KEY `IDX_2EAFFFFD50EAE44` (`id_utilisateur`),
  KEY `IDX_2EAFFFFD134FCDAC` (`id_cours`),
  CONSTRAINT `FK_2EAFFFFD134FCDAC` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON DELETE CASCADE,
  CONSTRAINT `FK_2EAFFFFD50EAE44` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favoris_cours`
--

LOCK TABLES `favoris_cours` WRITE;
/*!40000 ALTER TABLE `favoris_cours` DISABLE KEYS */;
/*!40000 ALTER TABLE `favoris_cours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favoris_salles`
--

DROP TABLE IF EXISTS `favoris_salles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favoris_salles` (
  `id_utilisateur` int NOT NULL,
  `id_salle` int NOT NULL,
  PRIMARY KEY (`id_utilisateur`,`id_salle`),
  KEY `IDX_43FB711750EAE44` (`id_utilisateur`),
  KEY `IDX_43FB7117A0123F6C` (`id_salle`),
  CONSTRAINT `FK_43FB711750EAE44` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE,
  CONSTRAINT `FK_43FB7117A0123F6C` FOREIGN KEY (`id_salle`) REFERENCES `salles` (`id_salle`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favoris_salles`
--

LOCK TABLES `favoris_salles` WRITE;
/*!40000 ALTER TABLE `favoris_salles` DISABLE KEYS */;
/*!40000 ALTER TABLE `favoris_salles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horaires`
--

DROP TABLE IF EXISTS `horaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horaires` (
  `id_horaire` int NOT NULL AUTO_INCREMENT,
  `jour_semaine` smallint NOT NULL,
  `heure_ouverture` time NOT NULL,
  `heure_fermeture` time NOT NULL,
  `id_salle` int NOT NULL,
  PRIMARY KEY (`id_horaire`),
  KEY `IDX_39B7118FA0123F6C` (`id_salle`),
  CONSTRAINT `FK_39B7118FA0123F6C` FOREIGN KEY (`id_salle`) REFERENCES `salles` (`id_salle`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horaires`
--

LOCK TABLES `horaires` WRITE;
/*!40000 ALTER TABLE `horaires` DISABLE KEYS */;
INSERT INTO `horaires` VALUES (1,1,'07:00:00','22:00:00',1),(2,2,'07:00:00','22:00:00',1),(3,3,'07:00:00','22:00:00',1),(4,4,'07:00:00','22:00:00',1),(5,5,'07:00:00','22:00:00',1),(6,6,'09:00:00','18:00:00',1),(7,1,'08:00:00','21:00:00',2),(8,2,'08:00:00','21:00:00',2),(9,3,'08:00:00','21:00:00',2),(10,4,'08:00:00','21:00:00',2),(11,5,'08:00:00','21:00:00',2),(12,6,'09:00:00','13:00:00',2),(13,1,'12:00:00','22:00:00',3),(14,2,'12:00:00','22:00:00',3),(15,3,'12:00:00','22:00:00',3),(16,4,'12:00:00','22:00:00',3),(17,5,'12:00:00','22:00:00',3),(18,6,'10:00:00','16:00:00',3);
/*!40000 ALTER TABLE `horaires` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,'2026-11-02 13:54:40','CONFIRMEE',11,2),(2,'2026-11-02 13:56:53','CONFIRMEE',11,1),(3,'2026-11-02 15:08:42','CONFIRMEE',11,5),(4,'2026-11-02 15:13:08','ANNULEE',11,8),(5,'2026-10-30 17:11:49','CONFIRMEE',12,15),(6,'2026-10-30 17:11:49','CONFIRMEE',11,15),(7,'2026-10-30 17:11:49','CONFIRMEE',12,16),(8,'2026-10-30 17:11:49','CONFIRMEE',11,16);
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
INSERT INTO `salles` VALUES (1,'FitZone Jette','Rue de la Forme 12','Jette','Grande salle polyvalente avec espace musculation et studio de cours collectifs.','02 555 01 01','/images/salle-fitzone.jpg',4),(2,'Studio Équilibre','Avenue du Bien-être 45','Ixelles','Studio lumineux dédié au yoga, au pilates et aux sports de combat.','02 555 02 02','/images/salle-equilibre.jpg',5),(3,'Boxing Club Nord','Boulevard des Sports 8','Schaerbeek','Club de boxe et de cardio avec ring, sacs et espace de pilates.','02 555 03 03','/images/salle-boxing.jpg',6);
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seances`
--

LOCK TABLES `seances` WRITE;
/*!40000 ALTER TABLE `seances` DISABLE KEYS */;
INSERT INTO `seances` VALUES (1,'2026-11-03 18:00:00','2026-11-03 19:00:00',1,1),(2,'2026-11-03 19:30:00','2026-11-03 20:30:00',4,3),(3,'2026-11-04 07:30:00','2026-11-04 08:20:00',8,4),(4,'2026-11-04 12:30:00','2026-11-04 13:30:00',3,2),(5,'2026-11-04 18:30:00','2026-11-04 19:15:00',5,3),(6,'2026-11-05 18:00:00','2026-11-05 19:15:00',2,1),(7,'2026-11-05 19:00:00','2026-11-05 19:50:00',7,2),(8,'2026-11-06 09:00:00','2026-11-06 09:55:00',6,4),(9,'2026-11-06 17:30:00','2026-11-06 18:30:00',1,1),(10,'2026-11-07 18:00:00','2026-11-07 19:00:00',4,3),(11,'2026-11-08 10:00:00','2026-11-08 10:50:00',7,2),(12,'2026-11-09 18:30:00','2026-11-09 19:30:00',3,2),(13,'2026-11-10 08:00:00','2026-11-10 08:50:00',8,4),(15,'2026-11-01 17:00:00','2026-11-01 18:00:00',1,1),(16,'2026-11-01 17:00:00','2026-11-01 18:00:00',5,3),(18,'2026-11-01 08:45:00','2026-11-01 09:30:00',8,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'Dupont','Marie','marie@test.be','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_SPORTIF',0,'2026-11-01'),(2,'Admin','FitBook','admin@fitbook.be','$2y$13$shs3wblNUfbGpPICvd.HyO5ID/CujvaN59XVUMVShi4fYct9uVnhK','ROLE_ADMIN',1,'2026-11-01'),(3,'test','test','test@test.be','$2y$13$fG/4ht9tejJLQwQnH9dXEuTYarhmH.PV1041mK1bTQ28ZUZTTRfXO','ROLE_SPORTIF',0,'2026-11-02'),(4,'Lampert','Sophie','gestionnaire1@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_GESTIONNAIRE',1,'2026-11-02'),(5,'Dupuis','Karim','gestionnaire2@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_GESTIONNAIRE',1,'2026-11-02'),(6,'Martens','Julie','gestionnaire3@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_GESTIONNAIRE',1,'2026-11-02'),(7,'Moreau','Nadia','coach1@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_COACH',1,'2026-11-02'),(8,'Peeters','Thomas','coach2@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_COACH',1,'2026-11-02'),(9,'Benali','Yassine','coach3@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_COACH',1,'2026-11-02'),(10,'Janssens','Laura','coach4@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_COACH',1,'2026-11-02'),(11,'Rossi','Marco','sportif1@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_SPORTIF',1,'2026-11-02'),(12,'Claes','Emma','sportif2@fitbook.test','$2y$13$X/qDEaXWFehOSMR23NFVi.NQ0wBHZBVYIOA74oJQhXCl0h7vIvWUe','ROLE_SPORTIF',1,'2026-11-02'),(14,'testtttt','testtttt','test55@test.be','$2y$13$YnB8Xp75ESINKqvcNMt1QOPjaMznHbm3GQjFlMjKcmIbmRbJjWRvO','ROLE_SPORTIF',1,'2026-11-02'),(15,'testeeeee','essss','test66@test.be','$2y$13$nl6inh0PZd9.WzSpkaTWw.ICiIivXgpZDRAoRGwuNBjXBdKF9Ra.G','ROLE_SPORTIF',1,'2026-11-02');
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'fitbook_final'
--
/*!50003 DROP PROCEDURE IF EXISTS `getHistoriqueSportif` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`fitbook_user`@`localhost` PROCEDURE `getHistoriqueSportif`(IN idUtilisateur INT)
BEGIN
    SELECT
        r.id_reservation,
        r.statut_reservation,
        r.date_reservation,
        s.date_heure_debut,
        s.date_heure_fin,
        c.titre_cours,
        sa.nom_salle,
        sa.ville_salle
    FROM reservations r
    INNER JOIN seances s ON s.id_seance = r.id_seance
    INNER JOIN cours c ON c.id_cours = s.id_cours
    INNER JOIN salles sa ON sa.id_salle = c.id_salle
    WHERE r.id_utilisateur = idUtilisateur
    ORDER BY s.date_heure_debut DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `getPlacesRestantes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`fitbook_user`@`localhost` PROCEDURE `getPlacesRestantes`(IN idSeance INT, OUT places INT)
BEGIN
    SELECT GREATEST(c.capacite_cours - COUNT(r.id_reservation), 0) INTO places
    FROM seances s
    INNER JOIN cours c ON c.id_cours = s.id_cours
    LEFT JOIN reservations r ON r.id_seance = s.id_seance AND r.statut_reservation = 'CONFIRMEE'
    WHERE s.id_seance = idSeance
    GROUP BY c.capacite_cours;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `getStatistiquesCoach` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`fitbook_user`@`localhost` PROCEDURE `getStatistiquesCoach`(IN idCoach INT)
BEGIN
    SELECT
        COUNT(*) AS seances_a_venir,
        COALESCE(SUM(t.reservees), 0) AS reservations_confirmees,
        COALESCE(ROUND(100 * SUM(t.reservees) / NULLIF(SUM(t.capacite), 0), 1), 0) AS taux_remplissage
    FROM (
        SELECT
            s.id_seance,
            c.capacite_cours AS capacite,
            (
                SELECT COUNT(*)
                FROM reservations r
                WHERE r.id_seance = s.id_seance AND r.statut_reservation = 'CONFIRMEE'
            ) AS reservees
        FROM seances s
        INNER JOIN cours c ON c.id_cours = s.id_cours
        WHERE s.id_coach = idCoach AND s.date_heure_debut >= NOW()
    ) AS t;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-03  1:05:11
