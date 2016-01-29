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
 * @filesource   $HeadURL: http://192.168.2.52/svn/berlussimo_1/tags/02.11.2010 - Downloadversion 0.27/import/config.php $
 * @version      $Revision: 6 $
 * @modifiedby   $LastChangedBy: sivac $
 * @lastmodified $Date: 2010-09-21 10:34:50 +0200 (Di, 21 Sep 2010) $
 * 
 */

/*
 * Das ist eine Test bzw. Spieledatei, aus der kann man viel lernen,
 * leider die Funktionen und vars nicht in Deutsch
 */
function connect() {
	$con = mysql_connect ( 'localhost', 'DBUSER', 'DBPASSWORD' ) or die ( mysql_error () );
	mysql_select_db ( 'DBNAME', $con ) or die ( mysql_error () );
}

connect ();

?>
