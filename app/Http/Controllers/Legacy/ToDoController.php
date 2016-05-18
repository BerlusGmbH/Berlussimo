<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\ToDoRequest;

class ToDoController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.todo.php';
    protected $include = 'legacy/options/modules/todo.php';

    public function request(ToDoRequest $request)
    {
        return $this->render();
    }
}