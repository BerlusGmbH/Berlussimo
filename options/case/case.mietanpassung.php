<?php
/* Created on / Erstellt am : 02.11.2010
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

if(isset($_REQUEST["daten"])){ 
$daten = $_REQUEST["daten"];
switch($daten) {

    case "mietanpassung":
    include("options/modules/mietanpassung.php");
    break;
    }
}

?>