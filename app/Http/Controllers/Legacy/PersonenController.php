<?php

namespace App\Http\Controllers\Legacy;


use App;
use App\Http\Controllers\Traits\Indexable;
use App\Http\Requests\Legacy\PersonenRequest;
use App\Http\Requests\Modules\Persons\StoreRequest;
use App\Http\Requests\Modules\Persons\UpdateRequest;
use App\Models\Details;
use App\Models\Person;
use App\Services\Parser\Lexer;
use App\Services\Parser\Parser;
use Carbon\Carbon;
use DB;
use ListViews;

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
            $query .= " " . ListViews::getView('v', request()->input('v'));
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
        $person = Person::with([
            'mietvertraege.einheit.haus.objekt',
            'kaufvertraege.einheit.haus.objekt',
            'details', 'audits', 'roles',
            'credential' => function ($query) {
                $query->withTrashed();
            }
        ])->findOrFail($id);
        return view('modules.personen.show', ['person' => $person]);
    }

    public function create(PersonenRequest $request)
    {
        if (session()->has('dublicates')) {
            $dublicates = session()->get('dublicates');
            return response()
                ->view('modules.personen.create_verify', ['dublicates' => $dublicates])
                ->header('Cache-Control', 'no-store');
        } else {
            return response()
                ->view('modules.personen.create')
                ->header('Cache-Control', 'no-store');
        }
    }

    public function store(StoreRequest $request)
    {
        return DB::transaction(function () {
            $person = Person::create(
                request()->only(['name', 'first_name', 'birthday'])
            );
            if (request()->has('phone')) {
                $phone = phone(request()->input('phone'), strtoupper(App::getLocale()));
                $person->details()->create([
                    'DETAIL_ID' => Details::max('DETAIL_ID') + 1,
                    'DETAIL_NAME' => 'Telefon',
                    'DETAIL_INHALT' => $phone,
                    'DETAIL_BEMERKUNG' => 'Stand: ' . Carbon::today()->toDateString(),
                    'DETAIL_AKTUELL' => '1'
                ]);
            }
            if (request()->has('email')) {
                $person->details()->create([
                    'DETAIL_ID' => Details::max('DETAIL_ID') + 1,
                    'DETAIL_NAME' => 'Email',
                    'DETAIL_INHALT' => request()->input('email'),
                    'DETAIL_BEMERKUNG' => 'Stand: ' . Carbon::today()->toDateString(),
                    'DETAIL_AKTUELL' => '1'
                ]);
            }
            if (request()->has('sex')) {
                $person->details()->create([
                    'DETAIL_ID' => Details::max('DETAIL_ID') + 1,
                    'DETAIL_NAME' => 'Geschlecht',
                    'DETAIL_INHALT' => request()->input('sex'),
                    'DETAIL_BEMERKUNG' => 'Stand: ' . Carbon::today()->toDateString(),
                    'DETAIL_AKTUELL' => '1'
                ]);
            }
            return redirect(route('web::personen::show', ['id' => $person->id]));
        });
    }

    public function update(UpdateRequest $request, Person $person)
    {
        $person->update(request()->only(['name', 'first_name', 'birthday']));
        return redirect()->back();
    }
}