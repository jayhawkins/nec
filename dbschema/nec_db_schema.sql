-- Valentina Studio --
-- MySQL dump --
-- ---------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
-- ---------------------------------------------------------


-- CREATE DATABASE "nec" -----------------------------------
CREATE DATABASE IF NOT EXISTS `nec` CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `nec`;
-- ---------------------------------------------------------


-- CREATE TABLE "contact_types" ----------------------------
-- CREATE TABLE "contact_types" --------------------------------
CREATE TABLE IF NOT EXISTS `contact_types` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "contacts" ---------------------------------
-- CREATE TABLE "contacts" -------------------------------------
CREATE TABLE IF NOT EXISTS `contacts` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`contactTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`firstName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`lastName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`emailAddress` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`primaryPhone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`secondaryPhone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`fax` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`contactRating` TinyInt( 2 ) UNSIGNED NOT NULL DEFAULT '0',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
ALTER TABLE contacts ADD COLUMN title VARCHAR(255) AFTER lastName;
ALTER TABLE contacts ADD COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Active' AFTER contactRating ;
-- ---------------------------------------------------------


-- CREATE TABLE "documents" --------------------------------
-- CREATE TABLE "documents" ------------------------------------
CREATE TABLE IF NOT EXISTS `documents` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`documentID` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`documentURL` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "email_templates" --------------------------
-- CREATE TABLE "email_templates" ------------------------------
CREATE TABLE IF NOT EXISTS `email_templates` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`title` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`body` Text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`subject` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "entities" ---------------------------------
-- CREATE TABLE "entities" -------------------------------------
CREATE TABLE IF NOT EXISTS `entities` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`comments` Text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`urls` JSON NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`logoURL` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`entityRating` TinyInt( 2 ) UNSIGNED NOT NULL DEFAULT '0',
	`assignedMemberID` Int( 11 ) UNSIGNED NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
ALTER TABLE entities ADD COLUMN createdAt DateTime NOT NULL AFTER assignedMemberID;
ALTER TABLE entities ADD COLUMN updatedAt DateTime NOT NULL AFTER createdAt;
-- ---------------------------------------------------------


-- CREATE TABLE "entity_types" -----------------------------
-- CREATE TABLE "entity_types" ---------------------------------
CREATE TABLE IF NOT EXISTS `entity_types` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "insurance_carriers" -----------------------
-- CREATE TABLE "insurance_carriers" ---------------------------
CREATE TABLE IF NOT EXISTS `insurance_carriers` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`contactName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`contactPhone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`policyNumber` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`policyExpirationDate` Date NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "links" ------------------------------------
-- CREATE TABLE "links" ----------------------------------------
CREATE TABLE IF NOT EXISTS `links` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`link` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL DEFAULT '0',
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "location_types" ---------------------------
-- CREATE TABLE "location_types" -------------------------------
CREATE TABLE IF NOT EXISTS `location_types` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "locations" --------------------------------
-- CREATE TABLE "locations" ------------------------------------
CREATE TABLE IF NOT EXISTS `locations` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`locationTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`address1` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`address2` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`city` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`state` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`zip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`latitude` Float( 10, 6 ) NOT NULL,
	`longitude` Float( 10, 6 ) NOT NULL,
	`timeZone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,ALTER TABLE members ADD COLUMN title VARCHAR(255) AFTER lastName;
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
ALTER TABLE locations ADD COLUMN name VarChar(255) NOT NULL AFTER locationTypeID;
ALTER TABLE locations ADD COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Active' AFTER timezone ;
-- ---------------------------------------------------------


-- CREATE TABLE "members" ----------------------------------
-- CREATE TABLE "members" --------------------------------------
CREATE TABLE IF NOT EXISTS `members` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`userID` Int( 11 ) UNSIGNED NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`firstName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`lastName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "object_type_data_points" ------------------
-- CREATE TABLE "object_type_data_points" ----------------------
CREATE TABLE IF NOT EXISTS `object_type_data_points` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`objectTypeID` Int( 11 ) NOT NULL,
	`columnName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`title` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
ALTER TABLE object_type_data_points MODIFY objectTypeID Int( 11 ) UNSIGNED NOT NULL;
-- ---------------------------------------------------------


-- CREATE TABLE "object_types" -----------------------------
-- CREATE TABLE "object_types" ---------------------------------
CREATE TABLE IF NOT EXISTS `object_types` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "objects" ----------------------------------
-- CREATE TABLE "objects" --------------------------------------
CREATE TABLE IF NOT EXISTS `objects` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`objectTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`data` JSON NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "regions" ----------------------------------
-- CREATE TABLE "regions" --------------------------------------
CREATE TABLE IF NOT EXISTS `regions` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`geofencingCoordinates` JSON NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "requisition_quotes" -----------------------
-- CREATE TABLE "requisition_quotes" ---------------------------
CREATE TABLE IF NOT EXISTS `requisition_quotes` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`requisitionID` Int( 11 ) UNSIGNED NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`memberID` Int( 11 ) UNSIGNED NOT NULL,
	`rate` Float( 9, 2 ) NOT NULL DEFAULT '0.00',
	`platesNeeded` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'No',
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Open',
	`notes` JSON NOT NULL,
	`originLocationID` Int( 11 ) UNSIGNED NOT NULL,
	`deliveryLocationID` Int( 11 ) UNSIGNED NOT NULL,
	`estimatedPickupDate` DateTime NOT NULL,
	`estimatedDeliveryDate` DateTime NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "requisition_tracking" ---------------------
-- CREATE TABLE "requisition_tracking" -------------------------
CREATE TABLE IF NOT EXISTS `requisition_tracking` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`requistionID` Int( 11 ) UNSIGNED NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`notes` JSON NOT NULL,
	`latitude` Float( 10, 6 ) UNSIGNED NOT NULL,
	`longitude` Float( 10, 6 ) UNSIGNED NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "requisitions" -----------------------------
-- CREATE TABLE "requisitions" ---------------------------------
CREATE TABLE IF NOT EXISTS `requisitions` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`objectID` Int( 11 ) UNSIGNED NOT NULL,
	`memberID` Int( 11 ) UNSIGNED NOT NULL,
	`contactID` Int( 11 ) UNSIGNED NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`releasedDate` DateTime NOT NULL,
	`notes` Text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originLocationID` Int( 11 ) UNSIGNED NOT NULL,
	`deliveryLocationID` Int( 11 ) UNSIGNED NOT NULL,
	`totalPayout` Float( 12, 2 ) NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "states" -----------------------------------
-- CREATE TABLE "states" ---------------------------------------
CREATE TABLE IF NOT EXISTS `states` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`abbreviation` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NULL,
	`updatedAt` DateTime NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "users" ------------------------------------
-- CREATE TABLE "users" ----------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`username` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`password` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Inactive',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ),
	CONSTRAINT `unique_username` UNIQUE( `username` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 1;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID1" --------------------------
-- CREATE INDEX "index_entityID1" ------------------------------
CREATE INDEX `index_entityID1` USING BTREE ON `contacts`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID2" --------------------------
-- CREATE INDEX "index_entityID2" ------------------------------
CREATE INDEX `index_entityID2` USING BTREE ON `insurance_carriers`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID3" --------------------------
-- CREATE INDEX "index_entityID3" ------------------------------
CREATE INDEX `index_entityID3` USING BTREE ON `links`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_name" -------------------------------
-- CREATE INDEX "index_name" -----------------------------------
CREATE INDEX `index_name` USING BTREE ON `links`( `name` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID" ---------------------------
-- CREATE INDEX "index_entityID" -------------------------------
CREATE INDEX `index_entityID` USING BTREE ON `locations`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID4" --------------------------
-- CREATE INDEX "index_entityID4" ------------------------------
CREATE INDEX `index_entityID4` USING BTREE ON `members`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_userID" -----------------------------
-- CREATE INDEX "index_userID" ---------------------------------
CREATE INDEX `index_userID` USING BTREE ON `members`( `userID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID5" --------------------------
-- CREATE INDEX "index_entityID5" ------------------------------
CREATE INDEX `index_entityID5` USING BTREE ON `objects`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID7" --------------------------
-- CREATE INDEX "index_entityID7" ------------------------------
CREATE INDEX `index_entityID7` USING BTREE ON `requisition_quotes`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID8" --------------------------
-- CREATE INDEX "index_entityID8" ------------------------------
CREATE INDEX `index_entityID8` USING BTREE ON `requisition_tracking`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID6" --------------------------
-- CREATE INDEX "index_entityID6" ------------------------------
CREATE INDEX `index_entityID6` USING BTREE ON `requisitions`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_objectID" ---------------------------
-- CREATE INDEX "index_objectID" -------------------------------
CREATE INDEX `index_objectID` USING BTREE ON `requisitions`( `objectID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------

-- After Everything has been checked --
ALTER TABLE `locations`
	ADD CONSTRAINT `lnk_entities_locations` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;

ALTER TABLE `members`
	ADD CONSTRAINT `lnk_entities_members` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;

ALTER TABLE `members`
	ADD CONSTRAINT `lnk_users_members` FOREIGN KEY ( `userID` )
	REFERENCES `users`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;

ALTER TABLE `locations`
	ADD CONSTRAINT `lnk_location_types_locations` FOREIGN KEY ( `locationTypeID` )
	REFERENCES `location_types`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;

ALTER TABLE `contacts`
	ADD CONSTRAINT `lnk_entities_contacts` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- ---------------------------------------------------------
