<?php

// http://deruwe.de/vorschaubilder-einfach-mit-php-realisieren-teil-2.html
class thumbnail {
	var $img;
	var $fileInfo;
	var $fullName;
	var $newX;
	var $newY;
	var $quality;
	var $orgX;
	var $orgY;
	
	// $data - (voller) Dateiname oder String (z.B. aus Datenbank)
	function create($data) {
		$this->destroy ();
		
		if (file_exists ( $data )) {
			$this->img = @ImageCreateFromJpeg ( $data );
			$this->fileInfo = basename ( $data );
			$this->fullName = $data;
		} else {
			$this->img = @ImageCreateFromString ( $data );
		}
		
		if (! $this->img) {
			$this->destroy ();
			return false;
		} else {
			$this->orgX = ImageSX ( $this->img );
			$this->orgY = ImageSY ( $this->img );
			return true;
		}
	}
	
	// Höhe des aktuellen Bildes im Container zurückgeben, false bei Fehler
	function height() {
		if ($this->img) {
			return ImageSY ( $this->img );
		} else {
			return false;
		}
	}
	
	// Breite des aktuellen Bildes im Container zurückgeben, false bei Fehler
	function width() {
		if ($this->img) {
			return ImageSX ( $this->img );
		} else {
			return false;
		}
	}
	
	// Qualität für Ausgabe setzen
	function setQuality($quality = false) {
		if ($this->img && $quality) {
			$this->quality = $quality;
		} else {
			return false;
		}
	}
	
	// Thumbnail erzeugen
	function resize($newX = false, $newY = false) {
		if ($this->img) {
			
			$X = ImageSX ( $this->img );
			$Y = ImageSY ( $this->img );
			
			$newX = $this->_convert ( $newX, $X );
			$newY = $this->_convert ( $newY, $Y );
			
			if (! $newX && ! $newY) {
				$newX = $X;
				$newY = $Y;
			}
			
			if (! $newX) {
				$newX = round ( $X / ($Y / $newY) );
			}
			
			if (! $newY) {
				$newY = round ( $Y / ($X / $newX) );
			}
			
			if (! $newimg = ImageCreateTruecolor ( $newX, $newY )) {
				$newimg = ImageCreate ( $newX, $newY );
			}
			
			if (! ImageCopyResampled ( $newimg, $this->img, 0, 0, 0, 0, $newX, $newY, $X, $Y )) {
				ImageCopyResized ( $newimg, $this->img, 0, 0, 0, 0, $newX, $newY, $X, $Y );
			}
			
			$this->img = $newimg;
			
			return true;
		} else {
			return false;
		}
	}
	
	// Schneidet ein Bild neu zu
	/*
	 * Werte für cut (X stellt das Ergebnis dar)
	 *
	 * $srcX
	 * +---+--------------+
	 * $srcY | | |
	 * +---+---+ |
	 * | | X | $newY | Ursprungsbild
	 * | +---+ |
	 * | $newX |
	 * | |
	 * +------------------+
	 */
	function cut($newX, $newY, $srcX = 0, $srcY = 0) {
		if ($this->img) {
			
			$X = ImageSX ( $this->img );
			$Y = ImageSY ( $this->img );
			
			$newX = $this->_convert ( $newX, $X );
			$newY = $this->_convert ( $newY, $Y );
			
			if (! $newX) {
				$newX = $X;
			}
			
			if (! $newY) {
				$newY = $Y;
			}
			
			if (! $newimg = ImageCreateTruecolor ( $X, $Y )) {
				$newimg = ImageCreate ( $X, $Y );
			}
			ImageCopy ( $newimg, $this->img, 0, 0, 0, 0, $X, $Y );
			ImageDestroy ( $this->img );
			if (! $this->img = ImageCreateTruecolor ( $newX, $newY )) {
				$this->img = ImageCreate ( $newX, $newY );
			}
			imagecopy ( $this->img, $newimg, 0, 0, $srcX, $srcY, $newX, $newY );
			ImageDestroy ( $newimg );
			
			return true;
		} else {
			return false;
		}
	}
	
	/*
	 * schneidet ein Teil mit Größe newX und newY an festgelegten Stellen des Bildes zu
	 * $pos = Position welches Teil verwendet werden soll
	 * +---+---+---+
	 * | 1 | 2 | 3 |
	 * +---+---+---+
	 * | 4 | 5 | 6 |
	 * +---+---+---+
	 * | 7 | 8 | 9 |
	 * +---+---+---+
	 */
	function autocut($newX, $newY, $pos = 5) {
		if ($this->img) {
			
			$X = ImageSX ( $this->img );
			$Y = ImageSY ( $this->img );
			
			$newX = $this->_convert ( $newX, $X );
			$newY = $this->_convert ( $newY, $Y );
			
			if (! $newX) {
				$newX = $X;
			}
			
			if (! $newY) {
				$newY = $Y;
			}
			
			switch ($pos) {
				case 1 :
					$srcX = 0;
					$srcY = 0;
					break;
				
				case 2 :
					$srcX = round ( ($X / 2) - ($newX / 2) );
					$srcY = 0;
					break;
				
				case 3 :
					$srcX = ImageSX ( $this->img ) - $newX;
					$srcY = 0;
					break;
				
				case 4 :
					$srcX = 0;
					$srcY = round ( ($Y / 2) - ($newY / 2) );
					break;
				
				case 5 :
					$srcX = round ( ($X / 2) - ($newX / 2) );
					$srcY = round ( ($Y / 2) - ($newY / 2) );
					break;
				
				case 6 :
					$srcX = $X - $newX;
					$srcY = round ( ($Y / 2) - ($newY / 2) );
					break;
				
				case 7 :
					$srcX = 0;
					$srcY = $Y - $newY;
					break;
				
				case 8 :
					$srcX = round ( ($X / 2) - ($newX / 2) );
					$srcY = $Y - $newY;
					break;
				
				case 9 :
					$srcX = $X - $newX;
					$srcY = $Y - $newY;
					break;
				
				default :
					$srcX = round ( ($X / 2) - ($newX / 2) );
					$srcY = round ( ($Y / 2) - ($newY / 2) );
			}
			
			return $this->cut ( $newX, $newY, $srcX, $srcY );
		} else {
			return false;
		}
	}
	
	// erzeugt ein Quadrat des Bildes mit Kantenlänge von $size
	// ist das Bild nicht quadratisch kann mit $pos
	// der Bildausschnitt festgelegt werden, Werte siehe function autocut
	function cube($size, $pos = 5) {
		if ($this->img) {
			
			$X = ImageSX ( $this->img );
			$Y = ImageSY ( $this->img );
			
			if ($X > $Y) {
				$newX = false;
				$newY = $size;
			} elseif ($X < $Y) {
				$newX = $size;
				$newY = false;
			} else {
				$newX = $size;
				$newY = $size;
			}
			
			if ($this->resize ( $newX, $newY )) {
				return $this->autocut ( $size, $size, $pos );
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	// erzeugt ein Bild dessen größte Kantenlänge $size ist
	function maxSize($size) {
		if ($this->img) {
			
			$X = ImageSX ( $this->img );
			$Y = ImageSY ( $this->img );
			
			if ($X > $Y) {
				$newX = $size;
				$newY = false;
			} elseif ($X < $Y) {
				$newX = false;
				$newY = $size;
			} else {
				$newX = $size;
				$newY = $size;
			}
			return $this->resize ( $newX, $newY );
		} else {
			return false;
		}
	}
	
	// erzeugt ein Bild dessen kleinste Kantenlänge $size ist
	function minSize($size) {
		if ($this->img) {
			
			$X = ImageSX ( $this->img );
			$Y = ImageSY ( $this->img );
			
			if ($X > $Y) {
				$newX = false;
				$newY = $size;
			} elseif ($X < $Y) {
				$newX = $size;
				$newY = false;
			} else {
				$newX = $size;
				$newY = $size;
			}
			return $this->resize ( $newX, $newY );
		} else {
			return false;
		}
	}
	
	// speichert das Bild als $fileName
	// wird $filename angegeben muss es ein voller Dateiname mit Pfad sein
	// ist $override wahr wird ein bestehendes Bild überschrieben, sonst nicht
	// Rückgabe:
	// true wenn geschrieben (oder überschrieben)
	// false on error
	// 0 wenn schon existiert (nur bei $override=false)
	function save($fileName, $override = true) {
		if ($this->img) {
			if (! file_exists ( $fileName ) || $override) {
				if (ImageJPEG ( $this->img, $fileName, $this->quality )) {
					return true;
				} else {
					return false;
				}
			} else {
				return 0;
			}
		} else {
			return false;
		}
	}
	
	// Gibt Bild an Browser aus (Ausgabe des Headers, Destroy aufrufen), beide optional
	function output($sendHeader = true, $destroy = true) {
		if ($this->img) {
			
			if ($sendHeader) {
				header ( "Content-type: image/jpeg" );
			}
			
			ImageJPEG ( $this->img, "", $this->quality );
			
			if ($destroy) {
				$this->destroy ();
			}
		} else {
			return false;
		}
	}
	
	// Setzt die Werte in der Klasse frei und löscht Bild
	function destroy() {
		if ($this->img) {
			ImageDestroy ( $this->img );
		}
		$this->img = false;
		$this->fileInfo = false;
		$this->fullName = false;
		$this->newX = false;
		$this->newY = false;
		$this->quality = 70;
		$this->orgX = false;
		$this->orgY = false;
	}
	
	// rechnet prozentuale Angaben in Pixel um, erwartet
	// ist $value eine Prozentangabe z.B. (string) "50%" wird diese umgerechnet
	// $full muss als 100% in Pixel angegeben werden
	function _convert($value, $full = false) {
		if (strstr ( $value, "%" )) {
			$value = trim ( str_replace ( "%", "", $value ) );
			$value = ($full / 100) * $value;
		}
		if ($value < 1 && $value !== false) {
			$value = 1;
		}
		return $value;
	}
}

