<?php

use App\Libraries\Permission;
use App\Libraries\Role;
use Illuminate\Database\Migrations\Migration;

class AddRolesAndPermissionsToPermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception|Throwable
     */
    public function up()
    {
        DB::transaction(function () {
            Permission::create(['name' => Permission::PERMISSION_MODUL_DETAIL]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_BENUTZER]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_BETRIEBSKOSTEN]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_BUCHEN]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_EINHEIT]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_BANKKONTO]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_HAUS]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_KASSE]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_KATALOG]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_KAUTION]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_KONTENRAHMEN]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_LAGER]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_LEERSTAND]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_OBJEKT]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_PARTNER]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_PERSON]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_PERSONAL]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_RECHNUNG]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_SEPA]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_STATISTIK]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_AUFTRAEGE]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_URLAUB]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_WEG]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_ZEITERFASSUNG]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_MIETVERTRAG]);
            Permission::create(['name' => Permission::PERMISSION_MODUL_WARTUNG]);

            $role = Role::create(['name' => Role::ROLE_ADMINISTRATOR]);
            $role->givePermissionTo(Permission::all()->pluck('name'));
            $role = Role::create(['name' => Role::ROLE_HAUSVERWALTER]);
            $role->givePermissionTo([
                Permission::PERMISSION_MODUL_DETAIL,
                Permission::PERMISSION_MODUL_BETRIEBSKOSTEN,
                Permission::PERMISSION_MODUL_BUCHEN,
                Permission::PERMISSION_MODUL_EINHEIT,
                Permission::PERMISSION_MODUL_BANKKONTO,
                Permission::PERMISSION_MODUL_HAUS,
                Permission::PERMISSION_MODUL_KATALOG,
                Permission::PERMISSION_MODUL_KAUTION,
                Permission::PERMISSION_MODUL_KONTENRAHMEN,
                Permission::PERMISSION_MODUL_LEERSTAND,
                Permission::PERMISSION_MODUL_MIETVERTRAG,
                Permission::PERMISSION_MODUL_OBJEKT,
                Permission::PERMISSION_MODUL_PARTNER,
                Permission::PERMISSION_MODUL_PERSON,
                Permission::PERMISSION_MODUL_RECHNUNG,
                Permission::PERMISSION_MODUL_SEPA,
                Permission::PERMISSION_MODUL_AUFTRAEGE,
                Permission::PERMISSION_MODUL_WEG,
                Permission::PERMISSION_MODUL_WARTUNG,
            ]);
            $role = Role::create(['name' => Role::ROLE_BUCHHALTER]);
            $role->givePermissionTo([
                Permission::PERMISSION_MODUL_BUCHEN,
                Permission::PERMISSION_MODUL_BANKKONTO,
                Permission::PERMISSION_MODUL_EINHEIT,
                Permission::PERMISSION_MODUL_HAUS,
                Permission::PERMISSION_MODUL_KATALOG,
                Permission::PERMISSION_MODUL_KAUTION,
                Permission::PERMISSION_MODUL_KONTENRAHMEN,
                Permission::PERMISSION_MODUL_LAGER,
                Permission::PERMISSION_MODUL_LEERSTAND,
                Permission::PERMISSION_MODUL_MIETVERTRAG,
                Permission::PERMISSION_MODUL_OBJEKT,
                Permission::PERMISSION_MODUL_PARTNER,
                Permission::PERMISSION_MODUL_PERSON,
                Permission::PERMISSION_MODUL_PERSONAL,
                Permission::PERMISSION_MODUL_RECHNUNG,
                Permission::PERMISSION_MODUL_SEPA,
                Permission::PERMISSION_MODUL_STATISTIK,
                Permission::PERMISSION_MODUL_URLAUB,
                Permission::PERMISSION_MODUL_WEG,
                Permission::PERMISSION_MODUL_ZEITERFASSUNG
            ]);
            $role = Role::create(['name' => Role::ROLE_BAULEITER]);
            $role->givePermissionTo([
                Permission::PERMISSION_MODUL_EINHEIT,
                Permission::PERMISSION_MODUL_BANKKONTO,
                Permission::PERMISSION_MODUL_HAUS,
                Permission::PERMISSION_MODUL_KATALOG,
                Permission::PERMISSION_MODUL_LAGER,
                Permission::PERMISSION_MODUL_LEERSTAND,
                Permission::PERMISSION_MODUL_OBJEKT,
                Permission::PERMISSION_MODUL_PARTNER,
                Permission::PERMISSION_MODUL_PERSON,
                Permission::PERMISSION_MODUL_RECHNUNG,
                Permission::PERMISSION_MODUL_STATISTIK,
                Permission::PERMISSION_MODUL_AUFTRAEGE,
                Permission::PERMISSION_MODUL_URLAUB,
                Permission::PERMISSION_MODUL_WEG,
                Permission::PERMISSION_MODUL_ZEITERFASSUNG
            ]);
            $role = Role::create(['name' => Role::ROLE_HAUSMEISTER]);
            $role->givePermissionTo([
                Permission::PERMISSION_MODUL_EINHEIT,
                Permission::PERMISSION_MODUL_HAUS,
                Permission::PERMISSION_MODUL_LEERSTAND,
                Permission::PERMISSION_MODUL_MIETVERTRAG,
                Permission::PERMISSION_MODUL_OBJEKT,
                Permission::PERMISSION_MODUL_PERSON,
                Permission::PERMISSION_MODUL_AUFTRAEGE,
                Permission::PERMISSION_MODUL_WEG,
                Permission::PERMISSION_MODUL_ZEITERFASSUNG
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
