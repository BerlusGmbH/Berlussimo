/* This is an example for a Parser in PHP */

%declare_class {class Parser}
%include_class
{
    // states whether the parse was successful or not
    public $successful = true;
    public $retvalue = 0;
    private $lex;
    private $query;
    private $internalError = false;
    private $class;
    private $r;
    private $rc;

    function __construct($lex, $query) {
        $this->lex = $lex;
        $this->query = $query;
        $this->class = get_class($query->getModel());
    }

    function stack($major, $count = 1) {
        $c = 0;
        for($i = count($this->yystack) - 1; $i >= 0; $i--)
        {
            if ($this->yystack[$i]->major === $major) {
                $c++;
                if($c == $count) {
                    return $this->yystack[$i]->minor;
                }
            }
        }
        return null;
    }
}

%parse_accept
{
    $this->successful = !$this->internalError;
    $this->internalError = false;
    $this->retvalue = $this->_retvalue;
}

%syntax_error
{
    $this->internalError = true;
    foreach ($this->yy_get_expected_tokens($yymajor) as $token) {
        $expect[] = $this->yyTokenName[$token];
    }
    throw new Exception('Unexpected ' . $this->tokenName($yymajor) . '(' . $TOKEN. '), expected one of: ' . implode(',', $expect));
}

start(res) ::= expressions(e).
{
    $q = $this->query;
    $withs = [];
    foreach($this->r as $with => $clause) {
        if($with === '') {
                $q->where(function($query) use ($clause) {
                    $query->mergeWheres(
                        $clause->getQuery()->wheres, $clause->getQuery()->getRawBindings()['where']
                    );
                });
                $q->getQuery()->orders = $clause->getQuery()->orders;
                if(in_array('App\Models\Traits\DefaultOrder', class_uses($q->getModel()))) {
                    $q->defaultOrder();
                }
        } else {
            $withs = array_merge($withs, [$with => function($query) use ($clause) {
                    $query->where(function($query) use ($clause) {
                        $query->mergeWheres(
                            $clause->getQuery()->wheres, $clause->getQuery()->getRawBindings()['where']
                        );
                    });
                    $query->getBaseQuery()->orders = $clause->getQuery()->orders;
                    if(in_array('App\Models\Traits\DefaultOrder', class_uses($query->getModel()))) {
                        $query->defaultOrder();
                    }
                }]
            );
        }
    }
    $q->with($withs);
    res = e;
}

expressions(res) ::= expressions(e1) expression(e2).
{
    if(empty(e1)) {
        e1 = collect();
    }
    if(isset(e2)) {
        e1->push(e2);
    }
    res = e1;
}
expressions(res) ::= expression(e).
{
    res = collect([e]);
    if(empty(e))
        res = null;
}
expression(res) ::= NOT expression.
{
    res = null;
}
expression(res) ::= COLUMN(co) clauseexpression(cl) viewexpression(v).
{
    $column = co;
    $clause = cl;
    $relationships = Relations::classColumnToRelations($this->class, $column);
    $views = v;
    foreach($relationships as $r) {
        if(isset($views) && !$views['columns']->isEmpty()) {
            $notWanted = true;
            foreach($views['columns'] as $col) {
                $rs = Relations::classColumnToRelations($this->class, $col);
                foreach($rs as $s) {
                    if(strpos($r, $s) !== false) {
                        $notWanted = false;
                    }
                }
            }
            if($notWanted) {
                continue;
            }
        }
        $query = $this->r[$r];
        if(isset($query)) {
            $query->getQuery()->mergeWheres($clause->getQuery()->wheres, $clause->getQuery()->getRawBindings()['where']);
        } else {
            $this->r[$r] = $clause;
        }
        $query = $this->r[$r];
        foreach($views['fields'] as $view) {
            $class = Relations::columnToClass($column);
            $field = Relations::classFieldToField($class, $view['field']);
            if(isset($view['order'])) {
                $query->orderBy($field, $view['order']);
            } else {
                $query->orderBy($field, 'asc');
            }
        }
        $this->r[$r] = $query;
    }
    res = [co => v];
}
clauseexpression(res) ::= .
{
    $class = Relations::columnToClass($this->stack(self::COLUMN));
    res = $class::query();
}
clauseexpression(res) ::= OPENP clauses(cl) CLOSEP.
{
    res = cl;
}
viewexpression ::= .
viewexpression(res) ::= OPENB views(v) CLOSEB. {
    res = v;
}
clause(res) ::= OR clause(c).
{
    c->getQuery()->wheres[0]['boolean'] = 'or';
    res = c;
}
clause(res) ::= AND clause(c). { res = c; }
clauses(res) ::= clauses(e) clause(t). { e->getQuery()->mergeWheres(t->getQuery()->wheres, t->getQuery()->getRawBindings()['where']); res = e; }
clauses(res) ::= clause(c). { res = c; }
clause(res) ::= term(t). { res = t; }
clause(res) ::= OR OPENP clauses(e) CLOSEP. { res = e->getModel()->newQuery()->orWhere(function($query) { $query->getQuery()->mergeWheres(e->getQuery()->wheres, e->getQuery()->getRawBindings()['where']); }); }
clause(res) ::= AND OPENP clauses(e) CLOSEP. { res = e->getModel()->newQuery()->where(function($query) { $query->getQuery()->mergeWheres(e->getQuery()->wheres, e->getQuery()->getRawBindings()['where']); }); }
clause(res) ::= COLUMN(co) OPENP clauses(cl) CLOSEP.
{
    $clause = cl;
    $column = co;
    $class = Relations::columnToClass($this->stack(self::COLUMN, 2));
    $relationships = Relations::classColumnToRelations($class, $column);
    $query = $class::query();
    $query->where(function($query) use ($relationships, $clause) {
        foreach($relationships as $r) {
            if($r === '') {
                $query->orWhere(function($query) use ($clause) {
                    $query->mergeWheres($clause->getQuery()->wheres, $clause->getQuery()->getRawBindings()['where']);
                });
            } else {
                $query->orWhereHas($r, function($query) use ($clause) {
                    $query->mergeWheres($clause->getQuery()->wheres, $clause->getQuery()->getRawBindings()['where']);
                });
            }
        }
    });
    res = $query;
}
clause(res) ::= COLUMN(co).
{
    $column = co;
    $class = Relations::columnToClass($this->stack(self::COLUMN, 2));
    $relationships = Relations::classColumnToRelations($class, $column);
    $query = $class::query();
    $query->where(function($query) use ($relationships) {
       foreach($relationships as $r) {
            if($r !== '') {
                $query->orHas($r);
            }
        }
    });
    res = $query;
}
term(res) ::= FIELD LIKE VALUE(v).
{
    $field = $this->stack(self::FIELD);
    $dbField = Relations::columnFieldToField($this->stack(self::COLUMN), $field);
    $class = Relations::columnToClass($this->stack(self::COLUMN));
    res = $class::where($dbField, 'like', '%' . v . '%');
}
term(res) ::= FIELD COMPARATOR(c) VALUE(v).
{
    $field = $this->stack(self::FIELD);
    $dbField = Relations::columnFieldToField($this->stack(self::COLUMN), $field);
    $class = Relations::columnToClass($this->stack(self::COLUMN));
    res = $class::where($dbField, c, v);
}
term(res) ::= LAUFZEIT EQ VALUE(v).
{
    $class = Relations::columnToClass($this->stack(self::COLUMN));
    res = $class::where(function($query) {
        $query->where(function($query) {
            $query->where('VON', '<=', v)->where('BIS', '>=', v);
        })->orWhere(function($query) {
            $query->where('VON', '<=', v)->whereNull('BIS');
        });
    });
}

views(res) ::= columnsorfields(v1) columnorfield(v2).
{
    v1['columns'] = v1['columns']->merge(v2['columns']);
    v1['fields'] = v1['fields']->merge(v2['fields']);
    res = v1;
}
columnsorfields(res) ::= columnorfield(v).
{
    res = v;
}
columnorfield(res) ::= field(v).
{
    res =  collect(['columns' => collect(), 'fields' => collect([v])]);
}
field(res)  ::= FIELD(f).
{
    res = [ 'field' => f ];
}
field(res)  ::= FIELD(f) COLON ORDER(o).
{
    res = [ 'field' => f, 'order' => o ];
}
columnorfield(res) ::= column(v).
{
    res =  collect(['columns' => v, 'fields' => collect()]);
}
column(res)  ::= COLUMN(f).
{
    res = collect(f);
}