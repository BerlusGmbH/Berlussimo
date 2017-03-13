<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Controllers\Traits\Indexable;
use App\Http\Requests\Legacy\ObjekteRequest;
use App\Services\Parser\Personen\View\Lexer;
use App\Services\Parser\Personen\View\Parser;
use App\Models\Objekte;

class ObjekteController extends LegacyController
{
    use Indexable;

    protected $submenu = 'legacy/options/links/links.form_objekte.php';
    protected $include = 'legacy/options/modules/objekte.php';

    public function request(ObjekteRequest $request)
    {
        return $this->render();
    }

    public function index(ObjekteRequest $request)
    {
        $builder = Objekte::query();
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

        $objekte = $builder->paginate(request()->input('s', 20));

        list($index, $wantedRelations) = $this->generateIndex($objekte, $columns);
        return view('modules.objekte.index', ['columns' => $columns, 'entities' => $objekte, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }

    public function select($id)
    {
        session()->put('objekt_id', $id);

        return redirect()->intended(route('web::legacy', [], false));
    }

    public function show($id, ObjekteRequest $request)
    {
        $objekt = Objekte::find($id);
        return view('modules.objekte.show', ['objekt' => $objekt]);
    }
}