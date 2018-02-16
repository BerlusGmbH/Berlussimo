<?php

namespace App\Libraries;

use Spatie\Permission\Models\Permission as PermissionContract;

class Permission extends PermissionContract
{
    const PERMISSION_MODUL_DETAIL = "modul detail";
    const PERMISSION_MODUL_BENUTZER = "modul benutzer";
    const PERMISSION_MODUL_BETRIEBSKOSTEN = "modul betriebskosten";
    const PERMISSION_MODUL_BUCHEN = "modul buchen";
    const PERMISSION_MODUL_EINHEIT = "modul einheit";
    const PERMISSION_MODUL_BANKKONTO = "modul bankkonto";
    const PERMISSION_MODUL_HAUS = "modul haus";
    const PERMISSION_MODUL_KASSE = "modul kasse";
    const PERMISSION_MODUL_KATALOG = "modul katalog";
    const PERMISSION_MODUL_KAUTION = "modul kaution";
    const PERMISSION_MODUL_KONTENRAHMEN = "modul kontenrahmen";
    const PERMISSION_MODUL_LAGER = "modul lager";
    const PERMISSION_MODUL_LEERSTAND = "modul leerstand";
    const PERMISSION_MODUL_OBJEKT = "modul objekt";
    const PERMISSION_MODUL_PARTNER = "modul partner";
    const PERMISSION_MODUL_PERSON = "modul person";
    const PERMISSION_MODUL_PERSONAL = "modul personal";
    const PERMISSION_MODUL_RECHNUNG = "modul rechnung";
    const PERMISSION_MODUL_SEPA = "modul sepa";
    const PERMISSION_MODUL_STATISTIK = "modul statistik";
    const PERMISSION_MODUL_AUFTRAEGE = "modul auftraege";
    const PERMISSION_MODUL_URLAUB = "modul urlaub";
    const PERMISSION_MODUL_WEG = "modul weg";
    const PERMISSION_MODUL_ZEITERFASSUNG = "modul zeiterfassung";
    const PERMISSION_MODUL_MIETVERTRAG = "modul mietvertrag";
    const PERMISSION_MODUL_WARTUNG = "modul wartung";
}