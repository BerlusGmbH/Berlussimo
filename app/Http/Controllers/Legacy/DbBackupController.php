<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\DbBackupRequest;

class DbBackupController extends LegacyController
{
    protected $include = 'legacy/options/modules/dbbackup.php';

    public function __construct()
    {
        parent::__construct();
        set_time_limit(300);
    }

    public function request(DbBackupRequest $request)
    {
        return $this->render();
    }
}