<?php
/*
 * Created on / Erstellt am : 13.01.2011
 * Author: Sivac
 */

/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright Copyright (c) 2010, Berlus GmbH, Fontanestr. 1, 14193 Berlin
 * @link http://www.berlus.de
 * @author Sanel Sivac & Wolfgang Wehrheim
 *         @contact software(@)berlus.de
 * @license http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *         
 * @filesource $HeadURL$
 * @version $Revision$
 *          @modifiedby $LastChangedBy$
 *          @lastmodified $Date$
 *         
 */
if (isset ( $_REQUEST ["daten"] )) {
	$daten = $_REQUEST ["daten"];
	switch ($daten) {
		
		case "todo" :
			include_once ("options/links/links.todo.php");
			echo "<div id='main'>";
			include ("options/modules/todo.php");
			echo "</div>";
			break;
	}
}

?>
