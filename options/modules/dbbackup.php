<?php
/**
 * BERLUSSIMO
 *
 * Hausverwaltungssoftware
 *
 *
 * @copyright    Copyright (c) 2011, Mark Letford, Charlottenstr. 38, 46049 Oberhausen
 * @link         http://www.letford.de
 * @author       Mark Letford
 * @contact		 post(@)letford.de
 * @license      http://www.gnu.org/licenses/agpl.html AGPL Version 3
 *
 * @version      $Revision: 1 $
 * @modifiedby   $LastChangedBy: Letford $
 * @lastmodified $Date: 2011-04-24 20:15:00 +0200 (Di, 24 Apr 2011) $
 *
 */
$f = new formular ();
$f->fieldset ( 'Datenbankzeilen sichern', 'db_sich' );
$ccyymmddhhmmss = date ( "Ymd-His" );
$sql_file = ROOT_PATH . "/backup/backup" . $ccyymmddhhmmss . ".sql";
$tar_file = ROOT_PATH . "/backup/backup" . $ccyymmddhhmmss . ".tar.gz";
if (! file_exists ( ROOT_PATH . "/backup" )) {
	if (! mkdir ( ROOT_PATH . "/backup", 0777 )) {
		die ( "Kann Verzeichnis " . ROOT_PATH . "/backup nicht anlegen!" );
	}
} else {
	echo "Backupverzeichnis existiert!<br>";
	$file = fopen ( "$sql_file", "w" ) or exit ( "Kein Schreibzugriff auf Backupdatei $sql_file" );
}
$line_count = create_backup ( $file );
fclose ( $file );
echo "Gespeicherte Zeilen: " . $line_count . "<br>";

if (BACKUP_COMPRESS == "0") {
	echo "Backupdatei <b>$sql_file</b> wurde erstellt.<br>";
} else {
	if (exec ( "tar -cvzf $tar_file $sql_file" )) {
		echo "<br>Backupdatei <b>$tar_file</b> wurde erstellt.<br>";
		unlink ( $sql_file );
	} else {
		echo "Backupdatei <b>$sql_file</b> wurde erstellt.<br>";
		echo "Die Datei konnte nicht gepackt werden!";
	}
}

if (! FTP_SERVER == "") {
	$connection_id = ftp_connect ( FTP_SERVER );
	$login_result = ftp_login ( $connection_id, FTP_USER, FTP_PASSWORD );
	if ((! $connection_id) || (! $login_result)) {
		echo "<H1>Ftp-Verbindung nicht hergestellt!<H1>";
		echo "<P>Verbindung mit FTP-Server nicht möglich!</P>";
		die ();
	} else {
		echo "<P>Verbunden mit FTP-Server</P>";
	}
	$remote_file = FTP_PATH . "/backup" . $ccyymmddhhmmss . ".sql";
	if (BACKUP_COMPRESS == "0") {
		$local_file = $sql_file;
	} else {
		$local_file = $tar_file;
	}
	$upload = ftp_put ( $connection_id, $remote_file, $local_file, FTP_BINARY );
	if (! $upload) {
		echo "<P>FTP-Upload war fehlerhaft!</P>";
	} else {
		echo "<P>Datei geschrieben.</P>";
	}
	unlink ( $local_file );
	$f->fieldset_ende ();
}
function create_backup($file) {
	$line_count = 0;
	$tables = mysql_list_tables ( DB_NAME );
	$sql_string = NULL;
	while ( $table = mysql_fetch_array ( $tables ) ) {
		$table_name = $table [0];
		$sql_string = "\nTRUNCATE $table_name;\n";
		$table_query = mysql_query ( "SELECT * FROM `$table_name`" );
		$num_fields = mysql_num_fields ( $table_query );
		while ( $fetch_row = mysql_fetch_array ( $table_query ) ) {
			$sql_string .= "INSERT INTO $table_name VALUES(";
			$first = TRUE;
			for($field_count = 1; $field_count <= $num_fields; $field_count ++) {
				if (TRUE == $first) {
					$sql_string .= "'" . mysql_real_escape_string ( $fetch_row [($field_count - 1)] ) . "'";
					$first = FALSE;
				} else {
					$sql_string .= ", '" . mysql_real_escape_string ( $fetch_row [($field_count - 1)] ) . "'";
				}
			}
			$sql_string .= ");";
			if ($sql_string != "") {
				$line_count = write_backup ( $file, $sql_string, $line_count );
			}
			$sql_string = NULL;
		}
	}
	return $line_count;
}
function write_backup($file, $string_in, $line_count) {
	ini_set ( 'display_errors', 'On' );
	if (fwrite ( $file, $string_in . "\n" )) {
		return ++ $line_count;
	} else {
		ini_set ( 'display_errors', 'On' );
		fehlermeldung_ausgeben ( "Schreibfehler! Kein Backuperstellt!" );
		die ();
	}
}

?>