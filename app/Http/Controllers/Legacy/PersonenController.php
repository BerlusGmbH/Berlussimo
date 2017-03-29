<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Controllers\Traits\Indexable;
use App\Http\Requests\Legacy\PersonenRequest;
use App\Http\Requests\Modules\Person\StorePersonRequest;
use App\Models\Person;
use App\Services\Parser\Lexer;
use App\Services\Parser\Parser;

class PersonenController extends LegacyController
{
    use Indexable;

    protected $submenu = 'legacy/options/links/links.person.php';
    protected $include = 'legacy/options/modules/person.php';

    public function request(PersonenRequest $request)
    {
        return $this->render();
    }

    public function index(PersonenRequest $request)
    {
        $builder = Person::with(['sex']);
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

        $personen = $builder->paginate(request()->input('s', 20));

        list($index, $wantedRelations) = $this->generateIndex($personen, $columns);
        return view('modules.personen.index', ['columns' => $columns, 'entities' => $personen, 'index' => $index, 'wantedRelations' => $wantedRelations]);
    }

    public function show($id, PersonenRequest $request)
    {
        $person = Person::with(['mietvertraege.einheit.haus.objekt', 'kaufvertraege.einheit.haus.objekt', 'details'])->findOrFail($id);
        return view('modules.personen.show', ['person' => $person]);
    }

    public function create(PersonenRequest $request)
    {
        return view('modules.personen.create');
    }

    public function store(StorePersonRequest $request)
    {
        $person = new Person();
        $person->name = trim(request()->input('name'));
        if(request()->has('first_name')) {
            $person->first_name = trim(request()->input('first_name'));
        }
        if(request()->has('birthday')) {
            $person->birthday = request()->input('birthday');
        }
        if(request()->has('sex')) {
            $person->first_name = trim(request()->input('first_name'));
        }
        $person->saveOrFail();
        $phone = phone($request->input('phone'));
        return $this->show($person->id, $request);
    }
}