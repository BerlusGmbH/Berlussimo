<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Controllers\Traits\Indexable;
use App\Http\Requests\Legacy\HaeuserRequest;
use App\Models\Haeuser;
use App\Services\Parser\Personen\View\Lexer;
use App\Services\Parser\Personen\View\Parser;

class HaeuserController extends LegacyController
{
    use Indexable;

    protected $submenu = 'legacy/options/links/links.form_haus.php';
    protected $include = 'legacy/options/modules/haus.php';

    public function request(HaeuserRequest $request)
    {
        return $this->render();
    }

    public function index(HaeuserRequest $request)
    {
        $builder = Haeuser::query();
        $query = "";
        if (request()->has('q')) {
            $query = request()->input('q');
        }
        if (request()->has('v')) {
            $query .= " " . request()->input('v');
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

        $haeuser = $builder->paginate(request()->input('s', 20));

        list($index, $wantedRelations) = $this->generateIndex($haeuser, $columns);
        return view('modules.haeuser.index', ['columns' => $columns, 'entities' => $haeuser, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }

    public function show($id, HaeuserRequest $request)
    {
        $haus = Haeuser::find($id);
        return view('modules.haeuser.show', ['haus' => $haus]);
    }
}