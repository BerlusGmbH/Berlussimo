<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\PersonenRequest;
use App\Models\Personen;
use App\Services\SearchParser\SearchLexer;
use App\Services\SearchParser\SearchParser;

class PersonenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.person.php';
    protected $include = 'legacy/options/modules/person.php';

    public function request(PersonenRequest $request)
    {
        return $this->render();
    }

    public function index() {
        $builder = Personen::with(['mietvertraege.einheit.haus.objekt', 'kaufvertraege.einheit.haus.objekt']);
        if (request()->has('q')) {
            $lexer = new SearchLexer(request()->input('q'));
            $parser = new SearchParser($lexer, $builder);
            while ($lexer->yylex())  {
                $parser->doParse($lexer->token, $lexer->value);
            }
            $parser->doParse(0, 0);
        }
        $personen = $builder->paginate(6);
        return view('modules.personen.index', ['personen' => $personen]);
    }

    public function show($id) {
        $person = Personen::with(['mietvertraege.einheit.haus.objekt', 'kaufvertraege.einheit.haus.objekt', 'details'])->find($id);
        return view('modules.personen.show', ['person' => $person]);
    }
}