<?php
/* Created on / Erstellt am : 21.09.2010
*  Author: Sivac
*/
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright    Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link         http://www.berlus.de
 * @author       Sanel Sivac & Wolfgang Wehrheim
 * @contact		 software(@)berlus.de
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * 
 * @filesource   $HeadURL$
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 * 
 */

/*Erstellung der Datenbanktabelle 'LIEFERSCHEINE' für die Erfassung von Lieferscheinen*/ 
$sql = 'CREATE TABLE `LIEFERSCHEINE` (`L_DAT` INT(7) NOT NULL AUTO_INCREMENT PRIMARY KEY, `L_ID` INT(7) NOT NULL, `DATUM` DATE NOT NULL, `LI_TYP` VARCHAR(50) NOT NULL, `LI_ID` INT(7) NOT NULL, `EMPF_TYP` VARCHAR(50) NOT NULL, `EMPF_ID` INT(7) NOT NULL, `L_NR` INT(30) NOT NULL, `AKTUELL` ENUM('0','1') NOT NULL) ENGINE = MyISAM'; 
?>
