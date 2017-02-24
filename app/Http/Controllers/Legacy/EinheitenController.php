<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Controllers\Traits\Indexable;
use App\Http\Requests\Legacy\EinheitenRequest;
use App\Models\Einheiten;
use App\Services\Parser\Personen\View\Lexer;
use App\Services\Parser\Personen\View\Parser;

class EinheitenController extends LegacyController
{
    use Indexable;

    protected $submenu = 'legacy/options/links/links.form_einheit.php';
    protected $include = 'legacy/options/modules/einheit.php';

    public function request(EinheitenRequest $request)
    {
        return $this->render();
    }

    public function index(EinheitenRequest $request)
    {
        $builder = Einheiten::query();
        $query = "";
        if (request()->has('q')) {
            $query = request()->input('q');
        }
        if (request()->has('v')) {
            $query .= " " . request()->input('v');
        }

        $trace = null;
        if (config('app.debug')) {
            $trace = fopen(storage_path('logs/vparser.log'), 'w');
        }
        $lexer = new Lexer($query, $trace);
        $parser = new Parser($lexer, $builder);
        $parser->Trace($trace, "\n");
        while ($lexer->yylex()) {
            $parser->doParse($lexer->token, $lexer->value);
        }
        $parser->doParse(0, 0);
        $columns = $parser->retvalue;

        if (request()->has('s')) {
            if (request()->input('s') != 'all') {
                $einheiten = $builder->paginate(request()->input('s'));
            } else {
                $einheiten = $builder->get();
            }
        } else {
            $einheiten = $builder->paginate(20);
        }
        list($index, $wantedRelations) = $this->generateIndex($einheiten, $columns);
        return view('modules.einheiten.index', ['columns' => $columns, 'entities' => $einheiten, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }

    public function show($id, EinheitenRequest $request)
    {
        $einheit = Einheiten::find($id);
        return view('modules.einheiten.show', ['einheit' => $einheit]);
    }
}