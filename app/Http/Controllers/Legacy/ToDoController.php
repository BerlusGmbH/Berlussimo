<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Controllers\Traits\Indexable;
use App\Http\Requests\Legacy\ToDoRequest;
use App\Models\Auftraege;
use App\Services\Parser\Lexer;
use App\Services\Parser\Parser;
use ListViews;

class ToDoController extends LegacyController
{
    use Indexable;

    protected $submenu = 'legacy/options/links/links.todo.php';
    protected $include = 'legacy/options/modules/todo.php';


    public function index(ToDoRequest $request)
    {
        $builder = Auftraege::query();
        $query = "";
        if (request()->has('q')) {
            $query = request()->input('q');
        }
        if (request()->has('v')) {
            $query .= " " . ListViews::getView('v', request()->input('v'));
        }
        if (request()->has('f')) {
            foreach (request()->input('f') as $f) {
                $query .= " " . ListViews::getView('f', $f);
            }
        }

        $trace = null;
        if (config('app.debug')) {
            $trace = fopen(storage_path('logs/parser.log'), 'w');
        }
        $lexer = new Lexer($query, $trace);
        $parser = new Parser($lexer, $builder);
        $parser->Trace($trace, "\n");
        while ($lexer->yylex()) {
            $parser->doParse($lexer->token, $lexer->value);
        }
        $parser->doParse(0, 0);
        $columns = $parser->retvalue;

        $auftraege = $builder->paginate(request()->input('s', 20));

        list($index, $wantedRelations) = $this->generateIndex($auftraege, $columns);
        return view('modules.auftraege.index', ['columns' => $columns, 'entities' => $auftraege, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }

    public function request(ToDoRequest $request)
    {
        return $this->render();
    }
}