<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Controllers\Traits\Indexable;
use App\Http\Requests\Legacy\BenutzerRequest;
use App\Models\User;
use App\Services\Parser\Lexer;
use App\Services\Parser\Parser;
use ListViews;

class BenutzerController extends LegacyController
{
    use Indexable;

    protected $submenu = 'legacy/options/links/links.benutzer.php';
    protected $include = 'legacy/options/modules/benutzer.php';

    public function request(BenutzerRequest $request)
    {
        return $this->render();
    }

    public function index(BenutzerRequest $request)
    {
        $builder = User::query();
        $query = "";
        if (request()->has('q')) {
            $query = request()->input('q');
        }
        if (request()->has('v')) {
            $query .= " " . ListViews::getView('v', request()->input('v'));
        }
        if (request()->has('f1')) {
            $query .= " " . ListViews::getView('f1', request()->input('f1'));
        }
        if (request()->has('f2')) {
            $query .= " " . ListViews::getView('f2', request()->input('f2'));
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

        $user = $builder->paginate(request()->input('s', 20));

        list($index, $wantedRelations) = $this->generateIndex($user, $columns);
        return view('modules.benutzer.index', ['columns' => $columns, 'entities' => $user, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }
}