<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\PersonenRequest;
use App\Models\Personen;
use App\Services\Parser\Personen\View\Lexer as VLexer;
use App\Services\Parser\Personen\View\Parser as VParser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PersonenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.person.php';
    protected $include = 'legacy/options/modules/person.php';

    public function request(PersonenRequest $request)
    {
        return $this->render();
    }

    public function index()
    {
        $builder = Personen::with(['sex']);
        $columns = [];
        $query = "";
        if (request()->has('v')) {
            $query = request()->input('v');
        }
        if (request()->has('q')) {
            $query .= " " . request()->input('q');
        }
        if (!empty($query)) {
            $trace = fopen(storage_path('logs/vparser.log'), 'w');
            $lexer = new VLexer($query, $trace);
            $parser = new VParser($lexer, $builder);
            $parser->Trace($trace, "\n");
            while ($lexer->yylex()) {
                $parser->doParse($lexer->token, $lexer->value);
            }
            $parser->doParse(0, 0);
            $columns = $parser->retvalue;
        }
        if (request()->has('s')) {
            if (request()->input('s') != 'all') {
                $personen = $builder->paginate(request()->input('s'));
            } else {
                $personen = $builder->get();
            }
        } else {
            $personen = $builder->paginate(5);
        }
        return view('modules.personen.index', ['personen' => $personen, 'columns' => $columns]);
    }

    public function show($id)
    {
        $person = Personen::with(['mietvertraege.einheit.haus.objekt', 'kaufvertraege.einheit.haus.objekt', 'details'])->find($id);
        return view('modules.personen.show', ['person' => $person]);
    }
}