<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\AdminRequest;

class AdminController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.admin_menu.php';
    protected $include = 'legacy/options/modules/admin_panel.php';

    public function request(AdminRequest $request)
    {
        return $this->render();
    }
}