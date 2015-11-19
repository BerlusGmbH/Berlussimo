<?php
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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/classes/config.inc.php $
 * @version      $Revision: 15 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2011-07-07 10:41:33 +0200 (Do, 07 Jul 2011) $
 * 
 */

define ("DB_USER", "DB_Username");
define ("DB_PASS", "DB_Password");
define ("DB_NAME", "Berlussimo_DB");
define ("DB_HOST", "localhost");

// Erg�nzungen f�r den FTP-Upload von Datenbanksicherungen
// Die Felder sind nur dann auszuf�llen, wenn ein FTP-Upload stattfinden soll

define ("FTP_SERVER", "");
define ("FTP_USER", "");
define ("FTP_PASSWORD", "");
define ("FTP_PATH", "");
define ("BACKUP_COMPRESS", "1"); //0 = no = (.sql), 1=yes=(.tar)

?>