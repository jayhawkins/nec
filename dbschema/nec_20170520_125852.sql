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


-- CREATE TABLE "carrier_needs" ----------------------------
-- CREATE TABLE "carrier_needs" --------------------------------
CREATE TABLE `carrier_needs` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`originationCity` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationState` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationZip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`destinationCity` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationState` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationZip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`originationLng` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationLat` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`destinationLng` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationLat` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`needsDataPoints` JSON NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Open',
	`qty` Smallint( 5 ) UNSIGNED NULL DEFAULT '0',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`contactEmails` JSON NOT NULL,
	`availableDate` Date NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 72;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "contact_types" ----------------------------
-- CREATE TABLE "contact_types" --------------------------------
CREATE TABLE `contact_types` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 4;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "contacts" ---------------------------------
-- CREATE TABLE "contacts" -------------------------------------
CREATE TABLE `contacts` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`contactTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`firstName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`lastName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`title` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`emailAddress` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`primaryPhone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`secondaryPhone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`fax` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`contactRating` TinyInt( 2 ) UNSIGNED NOT NULL DEFAULT '0',
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 11;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "documents" --------------------------------
-- CREATE TABLE "documents" ------------------------------------
CREATE TABLE `documents` ( 
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
CREATE TABLE `email_templates` ( 
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
AUTO_INCREMENT = 5;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "entities" ---------------------------------
-- CREATE TABLE "entities" -------------------------------------
CREATE TABLE `entities` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`comments` Text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`urls` JSON NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`logoURL` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`entityRating` TinyInt( 2 ) UNSIGNED NOT NULL DEFAULT '0',
	`assignedMemberID` Int( 11 ) UNSIGNED NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 32;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "entity_types" -----------------------------
-- CREATE TABLE "entity_types" ---------------------------------
CREATE TABLE `entity_types` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 3;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "insurance_carriers" -----------------------
-- CREATE TABLE "insurance_carriers" ---------------------------
CREATE TABLE `insurance_carriers` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`contactName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`contactPhone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`policyNumber` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`policyExpirationDate` Date NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 4;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "links" ------------------------------------
-- CREATE TABLE "links" ----------------------------------------
CREATE TABLE `links` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`link` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL DEFAULT '0',
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 3;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "location_types" ---------------------------
-- CREATE TABLE "location_types" -------------------------------
CREATE TABLE `location_types` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NULL DEFAULT '0',
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 9;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "locations" --------------------------------
-- CREATE TABLE "locations" ------------------------------------
CREATE TABLE `locations` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`locationTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`address1` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`address2` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`city` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`state` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`zip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`latitude` Float( 10, 6 ) NOT NULL,
	`longitude` Float( 10, 6 ) NOT NULL,
	`timeZone` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 67;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "locations_contacts" -----------------------
-- CREATE TABLE "locations_contacts" ---------------------------
CREATE TABLE `locations_contacts` ( 
	`location_id` Int( 11 ) UNSIGNED NOT NULL,
	`contact_id` Int( 11 ) UNSIGNED NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "members" ----------------------------------
-- CREATE TABLE "members" --------------------------------------
CREATE TABLE `members` ( 
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
AUTO_INCREMENT = 33;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "object_type_data_point_values" ------------
-- CREATE TABLE "object_type_data_point_values" ----------------
CREATE TABLE `object_type_data_point_values` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`object_type_data_point_id` Int( 11 ) UNSIGNED NOT NULL,
	`value` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`status` VarChar( 24 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 26;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "object_type_data_points" ------------------
-- CREATE TABLE "object_type_data_points" ----------------------
CREATE TABLE `object_type_data_points` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NULL DEFAULT '0',
	`objectTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`columnName` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`title` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 17;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "object_types" -----------------------------
-- CREATE TABLE "object_types" ---------------------------------
CREATE TABLE `object_types` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 2;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "objects" ----------------------------------
-- CREATE TABLE "objects" --------------------------------------
CREATE TABLE `objects` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`objectTypeID` Int( 11 ) UNSIGNED NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Active',
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
CREATE TABLE `regions` ( 
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
CREATE TABLE `requisition_quotes` ( 
	`id` Int( 11 ) UNSIGNED NOT NULL,
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
ENGINE = InnoDB;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "requisition_tracking" ---------------------
-- CREATE TABLE "requisition_tracking" -------------------------
CREATE TABLE `requisition_tracking` ( 
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
CREATE TABLE `requisitions` ( 
	`id` Int( 11 ) UNSIGNED NOT NULL,
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
ENGINE = InnoDB;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "states" -----------------------------------
-- CREATE TABLE "states" ---------------------------------------
CREATE TABLE `states` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`abbreviation` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`name` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`createdAt` DateTime NULL,
	`updatedAt` DateTime NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 52;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "users" ------------------------------------
-- CREATE TABLE "users" ----------------------------------------
CREATE TABLE `users` ( 
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
AUTO_INCREMENT = 25;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE TABLE "customer_needs" ---------------------------
-- CREATE TABLE "customer_needs" -------------------------------
CREATE TABLE `customer_needs` ( 
	`id` Int( 11 ) UNSIGNED AUTO_INCREMENT NOT NULL,
	`entityID` Int( 11 ) UNSIGNED NOT NULL,
	`originationCity` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationState` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationZip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`destinationCity` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationState` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationZip` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`originationLng` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`originationLat` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	`destinationLng` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`destinationLat` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
	`needsDataPoints` JSON NOT NULL,
	`status` VarChar( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Open',
	`qty` Smallint( 5 ) UNSIGNED NULL DEFAULT '0',
	`createdAt` DateTime NOT NULL,
	`updatedAt` DateTime NOT NULL,
	`contactEmails` JSON NOT NULL,
	`availableDate` Date NULL,
	CONSTRAINT `unique_id` UNIQUE( `id` ) )
CHARACTER SET = utf8
COLLATE = utf8_general_ci
ENGINE = InnoDB
AUTO_INCREMENT = 3;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID10" -------------------------
-- CREATE INDEX "index_entityID10" -----------------------------
CREATE INDEX `index_entityID10` USING BTREE ON `carrier_needs`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID1" --------------------------
-- CREATE INDEX "index_entityID1" ------------------------------
CREATE INDEX `index_entityID1` USING BTREE ON `contacts`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "lnk_contact_types_contacts" ---------------
-- CREATE INDEX "lnk_contact_types_contacts" -------------------
CREATE INDEX `lnk_contact_types_contacts` USING BTREE ON `contacts`( `contactTypeID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "lnk_entity_types_entities" ----------------
-- CREATE INDEX "lnk_entity_types_entities" --------------------
CREATE INDEX `lnk_entity_types_entities` USING BTREE ON `entities`( `entityTypeID` );
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


-- CREATE INDEX "lnk_location_types_locations" -------------
-- CREATE INDEX "lnk_location_types_locations" -----------------
CREATE INDEX `lnk_location_types_locations` USING BTREE ON `locations`( `locationTypeID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID9" --------------------------
-- CREATE INDEX "index_entityID9" ------------------------------
CREATE INDEX `index_entityID9` USING BTREE ON `locations_contacts`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_location_id" ------------------------
-- CREATE INDEX "index_location_id" ----------------------------
CREATE INDEX `index_location_id` USING BTREE ON `locations_contacts`( `location_id` );
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


-- CREATE INDEX "lnk_object_type_data_points_object_type_data_point_values" 
-- CREATE INDEX "lnk_object_type_data_points_object_type_data_point_values" 
CREATE INDEX `lnk_object_type_data_points_object_type_data_point_values` USING BTREE ON `object_type_data_point_values`( `object_type_data_point_id` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "lnk_object_types_object_type_data_points" -
-- CREATE INDEX "lnk_object_types_object_type_data_points" -----
CREATE INDEX `lnk_object_types_object_type_data_points` USING BTREE ON `object_type_data_points`( `objectTypeID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "index_entityID5" --------------------------
-- CREATE INDEX "index_entityID5" ------------------------------
CREATE INDEX `index_entityID5` USING BTREE ON `objects`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE INDEX "lnk_object_types_objects" -----------------
-- CREATE INDEX "lnk_object_types_objects" ---------------------
CREATE INDEX `lnk_object_types_objects` USING BTREE ON `objects`( `objectTypeID` );
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


-- CREATE INDEX "lnk_entities_customer_needs" --------------
-- CREATE INDEX "lnk_entities_customer_needs" ------------------
CREATE INDEX `lnk_entities_customer_needs` USING BTREE ON `customer_needs`( `entityID` );
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_carrier_needs" ----------------
-- CREATE LINK "lnk_entities_carrier_needs" --------------------
ALTER TABLE `carrier_needs`
	ADD CONSTRAINT `lnk_entities_carrier_needs` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_contact_types_contacts" ----------------
-- CREATE LINK "lnk_contact_types_contacts" --------------------
ALTER TABLE `contacts`
	ADD CONSTRAINT `lnk_contact_types_contacts` FOREIGN KEY ( `contactTypeID` )
	REFERENCES `contact_types`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_contacts" ---------------------
-- CREATE LINK "lnk_entities_contacts" -------------------------
ALTER TABLE `contacts`
	ADD CONSTRAINT `lnk_entities_contacts` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_locations" --------------------
-- CREATE LINK "lnk_entities_locations" ------------------------
ALTER TABLE `locations`
	ADD CONSTRAINT `lnk_entities_locations` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_location_types_locations" --------------
-- CREATE LINK "lnk_location_types_locations" ------------------
ALTER TABLE `locations`
	ADD CONSTRAINT `lnk_location_types_locations` FOREIGN KEY ( `locationTypeID` )
	REFERENCES `location_types`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_members" ----------------------
-- CREATE LINK "lnk_entities_members" --------------------------
ALTER TABLE `members`
	ADD CONSTRAINT `lnk_entities_members` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_users_members" -------------------------
-- CREATE LINK "lnk_users_members" -----------------------------
ALTER TABLE `members`
	ADD CONSTRAINT `lnk_users_members` FOREIGN KEY ( `userID` )
	REFERENCES `users`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_object_type_data_points_object_type_data_point_values" 
-- CREATE LINK "lnk_object_type_data_points_object_type_data_point_values" 
ALTER TABLE `object_type_data_point_values`
	ADD CONSTRAINT `lnk_object_type_data_points_object_type_data_point_values` FOREIGN KEY ( `object_type_data_point_id` )
	REFERENCES `object_type_data_points`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_object_types_object_type_data_points" --
-- CREATE LINK "lnk_object_types_object_type_data_points" ------
ALTER TABLE `object_type_data_points`
	ADD CONSTRAINT `lnk_object_types_object_type_data_points` FOREIGN KEY ( `objectTypeID` )
	REFERENCES `object_types`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


-- CREATE LINK "lnk_entities_customer_needs" ---------------
-- CREATE LINK "lnk_entities_customer_needs" -------------------
ALTER TABLE `customer_needs`
	ADD CONSTRAINT `lnk_entities_customer_needs` FOREIGN KEY ( `entityID` )
	REFERENCES `entities`( `id` )
	ON DELETE No Action
	ON UPDATE No Action;
-- -------------------------------------------------------------
-- ---------------------------------------------------------


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- ---------------------------------------------------------


