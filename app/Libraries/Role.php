<?php

namespace App\Libraries;

use Spatie\Permission\Models\Role as RoleContract;

class Role extends RoleContract
{
    const ROLE_ADMINISTRATOR = "administrator";
    const ROLE_HAUSVERWALTER = "hausverwalter";
    const ROLE_BUCHHALTER = "buchhalter";
    const ROLE_BAULEITER = "bauleiter";
    const ROLE_HAUSMEISTER = "hausmeister";
}