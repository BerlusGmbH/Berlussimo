#!/usr/local/bin/php
<?php
/*
 * Created on 27.05.2008
 *
 * Hausverwaltungssoftware BERLUSSIMO
 * Author: Berlus GmbH
 * Fontanestr. 1
 * D-14193 Berlin
 */
$datum_zeit = date("d.m.Y_H-i");
$dateiname = "berlussimo_$datum_zeit.sql";
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$tar_path = '/is/htdocs/wp1078767_QDZFG1A35S/www/berlus_de/berlussimo/backup';
$EMAIL_ADDR = 'sivac@berlus.de';
$EMAIL_FROM = 'backup@berlus.de';
$EMAIL_SUBJECT = "Backup vom $datum_zeit MYSQL TAR";
$backup_file = 'berlussimo_'.$datum_zeit.'.tar.gz';
include('/is/htdocs/wp1078767_QDZFG1A35S/www/berlus_de/berlussimo/includes/allgemeine_funktionen.php');
echo  @shell_exec("mysqldump --add-drop-table -c -n -h localhost --user=dbu1078767 --pass=7681_HAUSbau db1078767-berlus | gzip > $dateiname.gz");

//funktioniert
#mysqldump --add-drop-table -c -n -h localhost --user=dbu1078767 --pass=7681_HAUSbau db1078767-berlus | gzip > sanel.gz

if(file_exists("/is/htdocs/wp1078767_QDZFG1A35S/www/$dateiname.gz")){
echo "Ihr Backup wurde erstellt!";
}else{
	echo "Kein backup erstellt";
}


?>
