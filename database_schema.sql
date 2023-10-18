-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for e_learning
CREATE DATABASE IF NOT EXISTS `e_learning` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `e_learning`;

-- Dumping structure for table e_learning.admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `img` varchar(200) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `balance` float DEFAULT '0',
  PRIMARY KEY (`id_admin`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.admins: ~0 rows (approximately)
INSERT INTO `admins` (`id_admin`, `nom`, `prenom`, `email`, `img`, `mot_de_passe`, `balance`) VALUES
	(1, 'ABDELMOUMEN', 'Mustafa', 'e-learning@admin.com', 'users/avatars/default.png', 'admin123@@@', 148.23);

-- Dumping structure for table e_learning.apercus
CREATE TABLE IF NOT EXISTS `apercus` (
  `id_formation` int NOT NULL,
  `id_video` int NOT NULL,
  UNIQUE KEY `id_formation` (`id_formation`),
  UNIQUE KEY `id_video` (`id_video`),
  CONSTRAINT `fk_formations_apercus` FOREIGN KEY (`id_formation`) REFERENCES `formations` (`id_formation`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_videos_apercus` FOREIGN KEY (`id_video`) REFERENCES `videos` (`id_video`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.apercus: ~52 rows (approximately)

-- Dumping structure for table e_learning.bookmarks
CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id_etudiant` varchar(255) NOT NULL,
  `id_video` int NOT NULL,
  KEY `fk_etudiants_bookmarks` (`id_etudiant`),
  KEY `fk_videos_bookmarks` (`id_video`),
  CONSTRAINT `fk_etudiants_bookmarks` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON UPDATE CASCADE,
  CONSTRAINT `fk_videos_bookmarks` FOREIGN KEY (`id_video`) REFERENCES `videos` (`id_video`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.bookmarks: ~4 rows (approximately)

-- Dumping structure for table e_learning.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `image` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id_categorie`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.categories: ~30 rows (approximately)

-- Dumping structure for table e_learning.demande_paiements
CREATE TABLE IF NOT EXISTS `demande_paiements` (
  `id_payment` int NOT NULL AUTO_INCREMENT,
  `id_formateur` varchar(255) NOT NULL,
  `prix_demande` float NOT NULL,
  `date_de_demande` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `etat` varchar(10) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id_payment`),
  KEY `fk_formateurs_paiements` (`id_formateur`),
  CONSTRAINT `fk_formateurs_paiements` FOREIGN KEY (`id_formateur`) REFERENCES `formateurs` (`id_formateur`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.demande_paiements: ~0 rows (approximately)

-- Dumping structure for table e_learning.etudiants
CREATE TABLE IF NOT EXISTS `etudiants` (
  `id_etudiant` varchar(255) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `img` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `attempts` tinyint DEFAULT '0',
  `is_disabled` tinyint DEFAULT '0',
  `email_verified_at` datetime DEFAULT NULL,
  `verification_token` varchar(100) DEFAULT NULL,
  `expiration_token_at` datetime DEFAULT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `is_active` tinyint DEFAULT '0',
  PRIMARY KEY (`id_etudiant`) USING BTREE,
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.etudiants: ~32 rows (approximately)

-- Dumping structure for table e_learning.formateurs
CREATE TABLE IF NOT EXISTS `formateurs` (
  `id_formateur` varchar(255) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `img` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `attempts` tinyint DEFAULT '0',
  `is_disabled` tinyint DEFAULT '0',
  `paypalMail` varchar(250) DEFAULT NULL,
  `biographie` longtext,
  `balance` float NOT NULL DEFAULT '0',
  `is_active` tinyint DEFAULT '0',
  `code` varchar(50) DEFAULT NULL,
  `specialite` varchar(50) DEFAULT NULL,
  `id_categorie` int DEFAULT NULL,
  `is_all_info_present` tinyint DEFAULT '0',
  `slug` varchar(200) NOT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `verification_token` varchar(100) DEFAULT NULL,
  `expiration_token_at` datetime DEFAULT NULL,
  `facebook_profil` varchar(150) DEFAULT NULL,
  `linkedin_profil` varchar(150) DEFAULT NULL,
  `twitter_profil` varchar(150) DEFAULT NULL,
  `background_img` varchar(200) DEFAULT 'users/backgrounds/default.jpg',
  PRIMARY KEY (`id_formateur`) USING BTREE,
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_categories_formateurs` (`id_categorie`),
  CONSTRAINT `fk_categories_formateurs` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id_categorie`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.formateurs: ~33 rows (approximately)

-- Dumping structure for table e_learning.formations
CREATE TABLE IF NOT EXISTS `formations` (
  `id_formation` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL,
  `background_img` varchar(200) DEFAULT NULL,
  `mass_horaire` time NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prix` float(8,2) NOT NULL,
  `description` text NOT NULL,
  `jaimes` int DEFAULT '0',
  `fichier_attache` varchar(255) DEFAULT NULL,
  `etat` varchar(255) DEFAULT 'public',
  `id_langue` int DEFAULT '1',
  `id_niveau` int DEFAULT NULL,
  `id_formateur` varchar(255) DEFAULT NULL,
  `id_categorie` int DEFAULT NULL,
  `slug` varchar(200) NOT NULL,
  `can_join` tinyint DEFAULT '0',
  PRIMARY KEY (`id_formation`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_langues_formations` (`id_langue`),
  KEY `fk_categories_formations` (`id_categorie`),
  KEY `fk_formateurs_formations` (`id_formateur`),
  KEY `fk_niveaux_formations` (`id_niveau`),
  CONSTRAINT `fk_categories_formations` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id_categorie`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_formateurs_formations` FOREIGN KEY (`id_formateur`) REFERENCES `formateurs` (`id_formateur`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_langues_formations` FOREIGN KEY (`id_langue`) REFERENCES `langues` (`id_langue`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_niveaux_formations` FOREIGN KEY (`id_niveau`) REFERENCES `niveaux` (`id_niveau`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.formations: ~141 rows (approximately)

-- Dumping structure for procedure e_learning.group_formation_by_duration
DELIMITER //
CREATE PROCEDURE `group_formation_by_duration`()
BEGIN
	-- 'extraShort' => "'00' AND '01:00:59'",
	-- 'short' => "'01:00:00' AND '03:00:59'", 
	-- 'medium' => "'03:00:00' AND '06:00:59'",
	-- 'long' => "'06:00:00' AND '17:00:59'",
	-- 'extraLong' => "'17:00:00' AND '800:00'"
	
	SELECT
		'0 à 1 Heure' AS label,
		'extraShort' AS `value`,
		COUNT(*) AS total_formations
	FROM formations f
	WHERE etat = 'public'
	AND mass_horaire BETWEEN '00' AND '01:00:59'
	UNION
	SELECT
		'1 à 3 Heures' AS label,
		'short' AS `value`,
		COUNT(*)
	FROM formations f
	WHERE mass_horaire BETWEEN '01:00:00' AND '03:00:59'
	UNION
	SELECT
		'3 à 6 Heures' AS label,
		'medium' AS `value`,
		COUNT(*)
	FROM formations f
	WHERE etat = 'public'
	AND mass_horaire BETWEEN '03:00:00' AND '06:00:59'
	UNION
	SELECT
		'6 à 17 Heures' AS label,
		'long' AS `value`,
		COUNT(*)
	FROM formations f
	WHERE etat = 'public'
	AND mass_horaire BETWEEN '06:00:00' AND '17:00:59'
	UNION
	SELECT
		'Plus de 17 Heures' AS label,
		'extraLong' AS `value`,
		COUNT(*)
	FROM formations f
	WHERE etat = 'public'
	AND mass_horaire >= '17:00:00';
END//
DELIMITER ;

-- Dumping structure for table e_learning.inscriptions
CREATE TABLE IF NOT EXISTS `inscriptions` (
  `id_inscription` int NOT NULL AUTO_INCREMENT,
  `id_formation` int DEFAULT NULL,
  `id_etudiant` varchar(255) DEFAULT NULL,
  `id_formateur` varchar(255) DEFAULT NULL,
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prix` float NOT NULL,
  `transaction_info` json NOT NULL,
  `payment_id` varchar(100) NOT NULL,
  `payment_state` varchar(50) NOT NULL,
  `approval_url` text NOT NULL,
  PRIMARY KEY (`id_inscription`),
  KEY `fk_formateurs_inscriptions` (`id_formateur`),
  KEY `fk_etudiant_inscriptions` (`id_etudiant`),
  KEY `fk_formations_inscriptions` (`id_formation`),
  CONSTRAINT `fk_etudiant_inscriptions` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_formateurs_inscriptions` FOREIGN KEY (`id_formateur`) REFERENCES `formateurs` (`id_formateur`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_formations_inscriptions` FOREIGN KEY (`id_formation`) REFERENCES `formations` (`id_formation`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.inscriptions: ~38 rows (approximately)

-- Dumping structure for table e_learning.jaimes
CREATE TABLE IF NOT EXISTS `jaimes` (
  `id_etudiant` varchar(255) NOT NULL,
  `id_formation` int NOT NULL,
  KEY `fk_etudiants_jaimes` (`id_etudiant`),
  KEY `fk_formations_jaimes` (`id_formation`),
  CONSTRAINT `fk_etudiants_jaimes` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON UPDATE CASCADE,
  CONSTRAINT `fk_formations_jaimes` FOREIGN KEY (`id_formation`) REFERENCES `formations` (`id_formation`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.jaimes: ~22 rows (approximately)

-- Dumping structure for table e_learning.langues
CREATE TABLE IF NOT EXISTS `langues` (
  `id_langue` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(30) NOT NULL,
  PRIMARY KEY (`id_langue`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.langues: ~18 rows (approximately)
INSERT INTO `langues` (`id_langue`, `nom`) VALUES
	(1, 'Français'),
	(2, 'English'),
	(3, 'Español'),
	(4, 'العربية'),
	(5, 'Türkçe'),
	(6, 'Português'),
	(7, 'Deutsch'),
	(8, 'Italiano'),
	(9, 'Русский'),
	(10, '日本語'),
	(11, '中文'),
	(12, 'Polski'),
	(13, 'हिन्दी'),
	(14, 'Nederlands'),
	(15, 'Română'),
	(16, 'ไทย'),
	(17, 'اردو'),
	(18, 'বাংলা');

-- Dumping structure for table e_learning.messages
CREATE TABLE IF NOT EXISTS `messages` (
  `id_message` bigint NOT NULL AUTO_INCREMENT,
  `from` varchar(50) NOT NULL DEFAULT '0',
  `to` varchar(50) NOT NULL DEFAULT '0',
  `message` longtext NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_message`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table e_learning.messages: ~92 rows (approximately)

-- Dumping structure for table e_learning.niveaux
CREATE TABLE IF NOT EXISTS `niveaux` (
  `id_niveau` int NOT NULL,
  `nom` varchar(50) NOT NULL,
  `icon` text,
  PRIMARY KEY (`id_niveau`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.niveaux: ~3 rows (approximately)
INSERT INTO `niveaux` (`id_niveau`, `nom`, `icon`) VALUES
	(1, 'Débutant', '<svg width="58" height="30" viewBox="0 0 38 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#E5E5E5" d="M9 4h6v2H9zM23 4h6v2h-6z"></path><circle cx="5" cy="5" r="5" fill="#662d91"></circle><circle fill="#E5E5E5" cx="19" cy="5" r="5"></circle><circle fill="#E5E5E5" cx="33" cy="5" r="5"></circle></svg>'),
	(2, 'Intermédiaire', '<svg width="58" height="30" viewBox="0 0 38 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 4h6v2H9z" fill="#662d91"></path><path d="M23 4h6v2h-6z" fill="#E5E5E5"></path><circle cx="5" cy="5" r="5" fill="#662d91"></circle><circle cx="19" cy="5" r="5" fill="#662d91"></circle><circle fill="#E5E5E5" cx="33" cy="5" r="5"></circle></svg>'),
	(3, 'Avancé', '<svg width="58" height="30" viewBox="0 0 38 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#662d91" d="M9 4h6v2H9zM23 4h6v2h-6z"></path><circle cx="5" cy="5" r="5" fill="#662d91"></circle><circle fill="#662d91" cx="19" cy="5" r="5"></circle><circle fill="#662d91" cx="33" cy="5" r="5"></circle></svg>');

-- Dumping structure for table e_learning.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id_notification` bigint NOT NULL AUTO_INCREMENT,
  `content` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `is_read` tinyint NOT NULL DEFAULT '0',
  `url` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `icon` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sender_id` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `recipient_id` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id_notification`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table e_learning.notifications: ~3 rows (approximately)

-- Dumping structure for table e_learning.reinitialisations_de_mot_de_passe
CREATE TABLE IF NOT EXISTS `reinitialisations_de_mot_de_passe` (
  `email` varchar(50) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expired_at` datetime NOT NULL,
  `type_utilisateur` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table e_learning.reinitialisations_de_mot_de_passe: ~0 rows (approximately)

-- Dumping structure for table e_learning.videos
CREATE TABLE IF NOT EXISTS `videos` (
  `id_video` int NOT NULL AUTO_INCREMENT,
  `id_formation` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `url` varchar(200) NOT NULL,
  `duree` time NOT NULL,
  `description` text NOT NULL,
  `ordre` int DEFAULT '999',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `thumbnail` varchar(120) DEFAULT NULL,
  PRIMARY KEY (`id_video`),
  KEY `fk_formations_videos` (`id_formation`),
  CONSTRAINT `fk_formations_videos` FOREIGN KEY (`id_formation`) REFERENCES `formations` (`id_formation`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Dumping data for table e_learning.videos: ~252 rows (approximately)

-- Dumping structure for table e_learning.sous_titres
CREATE TABLE `sous_titres` (
	`id_sous_titre` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`source` VARCHAR(255) NOT NULL,
	`id_video` INT(10) NOT NULL,
	`id_langue` INT(10) NULL DEFAULT NULL,
	PRIMARY KEY (`id_sous_titre`),
	CONSTRAINT `fk_langues_sous_titres` FOREIGN KEY (`id_langue`) REFERENCES `langues` (`id_langue`) ON UPDATE CASCADE ON DELETE SET NULL,
	CONSTRAINT `fk_videos_sous_titres` FOREIGN KEY (`id_video`) REFERENCES `videos` (`id_video`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


-- Dumping structure for trigger e_learning.demande_paiements_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `demande_paiements_after_update` AFTER UPDATE ON `demande_paiements` FOR EACH ROW BEGIN

  IF(NEW.etat = 'accepted') THEN 

    -- la table formateurs

    UPDATE formateurs

    SET balance = balance - NEW.prix_demande

    WHERE id_formateur = NEW.id_formateur;

    -- la table admin

    UPDATE admins

    SET balance = balance - NEW.prix_demande;

  END IF;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger e_learning.etudiants_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `etudiants_before_insert` BEFORE INSERT ON `etudiants` FOR EACH ROW BEGIN

  DECLARE numero INT;

  DECLARE v_id_etudiant VARCHAR(100);

  DECLARE is_exist BOOLEAN DEFAULT FALSE;

  DECLARE cpt INT;

  DECLARE new_slug VARCHAR(200);
  
  DECLARE suffix INT DEFAULT 1;

  SET numero = (SELECT COUNT(*) FROM etudiants) + 1;

  SET v_id_etudiant = CONCAT("ETU", numero);

  check_exist : LOOP

    SET cpt = (SELECT COUNT(*) FROM etudiants WHERE id_etudiant = v_id_etudiant);

    IF(cpt = 0) THEN

      LEAVE check_exist;

    ELSE

      SET numero = numero + 1;

      SET v_id_etudiant = CONCAT("ETU", numero);

    END IF;

  END LOOP check_exist;

  

  SET NEW.id_etudiant = v_id_etudiant;
  
  
  SET new_slug = LOWER(REPLACE(CONCAT(NEW.nom, ' ', NEW.prenom), ' ', '-'));

  WHILE EXISTS(SELECT 1 FROM etudiants WHERE slug = new_slug) DO

    SET new_slug = CONCAT(LOWER(REPLACE(NEW.nom, ' ', '-')), '-', suffix);

    SET suffix = suffix + 1;

  END WHILE;

  SET NEW.slug = new_slug;

	IF NEW.img IS NULL THEN
		# change default avatar path from here 
      SET NEW.img = 'users/avatars/default.png';
   END IF;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger e_learning.formateurs_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `formateurs_before_insert` BEFORE INSERT ON `formateurs` FOR EACH ROW BEGIN

  DECLARE numero INT;

  DECLARE v_id_formateur VARCHAR(100);

  DECLARE is_exist BOOLEAN DEFAULT FALSE;

  DECLARE cpt INT;

  DECLARE new_slug VARCHAR(200);

  DECLARE suffix INT DEFAULT 1;

  

  SET numero = (SELECT COUNT(*) FROM formateurs) + 1;

  SET v_id_formateur = CONCAT("FOR", numero);

  check_exist : LOOP

    SET cpt = (SELECT COUNT(*) FROM formateurs WHERE id_formateur = v_id_formateur);

    IF(cpt = 0) THEN

      LEAVE check_exist;

    ELSE

      SET numero = numero + 1;

      SET v_id_formateur = CONCAT("FOR", numero);

    END IF;

  END LOOP check_exist;

  	SET NEW.id_formateur = v_id_formateur;
  
  	SET new_slug = LOWER(REPLACE(CONCAT(NEW.nom, ' ', NEW.prenom), ' ', '-'));

   WHILE EXISTS(SELECT 1 FROM formateurs WHERE slug = new_slug) DO
   	SET new_slug = CONCAT(LOWER(REPLACE(NEW.nom, ' ', '-')), '-', suffix);
   	SET suffix = suffix + 1;
   END WHILE;

   SET NEW.slug = new_slug;
  
  	IF NEW.img IS NULL THEN
		# change default avatar path from here 
      SET NEW.img = 'users/avatars/default.png';
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger e_learning.formations_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `formations_before_insert` BEFORE INSERT ON `formations` FOR EACH ROW BEGIN

  DECLARE new_slug VARCHAR(255);

  DECLARE suffix INT DEFAULT 1;



  SET new_slug = LOWER(REPLACE(NEW.nom, ' ', '-'));



  WHILE EXISTS(SELECT 1 FROM formations WHERE slug = new_slug) DO

    SET new_slug = CONCAT(LOWER(REPLACE(NEW.nom, ' ', '-')), '-', suffix);

    SET suffix = suffix + 1;

  END WHILE;



  SET NEW.slug = new_slug;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger e_learning.inscriptions_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `inscriptions_after_update` AFTER UPDATE ON `inscriptions` FOR EACH ROW BEGIN

  UPDATE admins

  SET balance = balance + NEW.prix;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger e_learning.jaimes_after_delete
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `jaimes_after_delete` AFTER DELETE ON `jaimes` FOR EACH ROW BEGIN

	/* calcLikeDelete */

  DECLARE likesCount int DEFAULT 0;

    SET likesCount=(SELECT count(*) FROM jaimes WHERE id_formation=OLD.id_formation);

  UPDATE formations SET jaimes=likesCount WHERE id_formation=OLD.id_formation;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger e_learning.jaimes_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `jaimes_after_insert` AFTER INSERT ON `jaimes` FOR EACH ROW BEGIN

	/* calcLikeInsert */

  DECLARE likesCount int DEFAULT 0;

    SET likesCount=(SELECT count(*) FROM jaimes WHERE id_formation=NEW.id_formation);

  UPDATE formations f SET jaimes=likesCount WHERE id_formation=NEW.id_formation;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger e_learning.videos_after_delete
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `videos_after_delete` AFTER DELETE ON `videos` FOR EACH ROW BEGIN

	/* calcDureeOnDelete */

  UPDATE formations f SET mass_horaire=

    (SELECT  SEC_TO_TIME(SUM(TIME_TO_SEC(v.duree))) 

    FROM videos v WHERE v.id_formation=OLD.id_formation)

    WHERE f.id_formation=OLD.id_formation;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger e_learning.videos_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `videos_after_insert` AFTER INSERT ON `videos` FOR EACH ROW BEGIN

	/* calcDuree */

  UPDATE formations f SET mass_horaire=

    (SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(v.duree))) 

    FROM videos v WHERE v.id_formation=NEW.id_formation)

    WHERE f.id_formation=NEW.id_formation;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger e_learning.videos_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `videos_after_update` AFTER UPDATE ON `videos` FOR EACH ROW BEGIN

	/* calcDureeOnUpdate */

  UPDATE formations f SET mass_horaire=

    (SELECT  SEC_TO_TIME(SUM(TIME_TO_SEC(v.duree))) 

    FROM videos v WHERE v.id_formation=NEW.id_formation)

    WHERE f.id_formation=NEW.id_formation;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;