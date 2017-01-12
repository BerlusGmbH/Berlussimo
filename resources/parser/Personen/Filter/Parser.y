/* This is an example for a Parser in PHP */

%declare_class {class Parser extends Base}
%include_class
{
    // states whether the parse was successful or not
    public $successful = true;
    public $retvalue = 0;
    private $lex;
    private $query;
    private $internalError = false;

    function __construct($lex, $query) {
        $this->lex = $lex;
        $this->query = $query;
    }
}

%parse_accept
{
    $this->successful = !$this->internalError;
    $this->internalError = false;
    $this->retvalue = $this->query;
}

%syntax_error
{
    $this->internalError = true;
    foreach ($this->yy_get_expected_tokens($yymajor) as $token) {
        $expect[] = $this->yyTokenName[$token];
    }
    throw new Exception('Unexpected ' . $this->tokenName($yymajor) . '(' . $TOKEN. '), expected one of: ' . implode(',', $expect));
}

start(res) ::= expression(e). { $this->query->getQuery()->mergeWheres(e->getQuery()->wheres, e->getQuery()->getRawBindings()['where']); res = $this->query; }

expression(res) ::= expression(e) andexpression(t). { e->getQuery()->mergeWheres(t->getQuery()->wheres, t->getQuery()->getRawBindings()['where']); res = e; }
expression(res) ::= expression(e) AND andexpression(t). { e->getQuery()->mergeWheres(t->getQuery()->wheres, t->getQuery()->getRawBindings()['where']); res = e; }
expression(res) ::= expression(e) OR orexpression(t). { e->getQuery()->mergeWheres(t->getQuery()->wheres, t->getQuery()->getRawBindings()['where']); res = e; }
expression(res) ::= andexpression(e). { res = e; }
expression(res) ::= OPENP andexpression(e) CLOSEP. { res = e; }
orexpression(res) ::= term(t).
{
    $term = t;
    res = Personen::orWhere(function($query) use ($term) {
        $query->getQuery()->mergeWheres($term->getQuery()->wheres, $term->getQuery()->getRawBindings()['where']);
    });
}
andexpression(res) ::= term(t). { res = t; }
orexpression(res) ::= OPENP expression(e) CLOSEP. { res = Personen::orWhere(function($query) { $query->getQuery()->mergeWheres(e->getQuery()->wheres, e->getQuery()->getRawBindings()['where']); }); }
andexpression(res) ::= OPENP expression(e) CLOSEP. { res = Personen::where(function($query) { $query->getQuery()->mergeWheres(e->getQuery()->wheres, e->getQuery()->getRawBindings()['where']); }); }
term(res) ::= VALUE(v). { res = Personen::search(v); }
term(res) ::= PERSON_NAME EQ VALUE(v).
{
    res = Personen::where('PERSON_NACHNAME', '=', v);
}
term(res) ::= PERSON_NAME LIKE VALUE(v).
{
    res = Personen::where('PERSON_NACHNAME', 'like', '%' . v . '%');
}
term(res) ::= PERSON_VORNAME EQ VALUE(v).
{
    res = Personen::where('PERSON_VORNAME', '=', v);
}
term(res) ::= PERSON_VORNAME LIKE VALUE(v).
{
    res = Personen::where('PERSON_VORNAME', 'like', '%' . v . '%');
}
term(res) ::= PERSON_ID EQ VALUE(v).
{
    res = Personen::where('PERSON_ID', '=', v);
}
term(res) ::= PERSON_ID LIKE VALUE(v).
{
    res = Personen::where('PERSON_ID', 'like', '%' . v . '%');
}
term(res) ::= EINHEIT_NAME EQ VALUE(v).
{
    res = $this->einheitNameEqValue(v);
}
term(res) ::= EINHEIT_NAME LIKE VALUE(v).
{
    res = $this->einheitNameLikeValue(v);
}
term(res) ::= EINHEIT_LAGE EQ VALUE(v).
{
    res = $this->einheitLageEqValue(v);
}
term(res) ::= EINHEIT_LAGE LIKE VALUE(v).
{
    res = $this->einheitLageLikeValue(v);
}
term(res) ::= EINHEIT_ID EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit', function($query) { $query->where('EINHEIT_ID', '=', v); });
}
term(res) ::= EINHEIT_ID LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit', function($query) { $query->where('EINHEIT_ID', 'like', '%' . v . '%'); });
}
term(res) ::= HAUS_STRASSE EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_STRASSE', '=', v); });
}
term(res) ::= HAUS_STRASSE LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_STRASSE', 'like', '%' . v . '%'); });
}
term(res) ::= HAUS_NUMMER EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_NUMMER', '=', v); });
}
term(res) ::= HAUS_NUMMER LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_NUMMER', 'like', '%' . v . '%'); });
}
term(res) ::= HAUS_PLZ EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_PLZ', '=', v); });
}
term(res) ::= HAUS_PLZ LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_PLZ', 'like', '%' . v . '%'); });
}
term(res) ::= HAUS_ORT EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_ORT', '=', v); });
}
term(res) ::= HAUS_ORT LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_ORT', 'like', '%' . v . '%'); });
}
term(res) ::= HAUS_ID EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_ID', '=', v); });
}
term(res) ::= HAUS_ID LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_ID', 'like', '%' . v . '%'); });
}
term(res) ::= OBJEKT_NAME EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus.objekt', function($query) { $query->where('OBJEKT_KURZNAME', '=', v); });
}
term(res) ::= OBJEKT_NAME LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus.objekt', function($query) { $query->where('OBJEKT_KURZNAME', 'like', '%' . v . '%'); });
}
term(res) ::= OBJEKT_ID EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus.objekt', function($query) { $query->where('OBJEKT_ID', '=', v); });
}
term(res) ::= OBJEKT_ID LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege.einheit.haus.objekt', function($query) { $query->where('OBJEKT_ID', 'like', '%' . v . '%'); });
}
term(res) ::= MIETVERTRAG_VON LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege', function($query) { $query->where('MIETVERTRAG_VON', 'like', '%' . v . '%'); });
}
term(res) ::= MIETVERTRAG_VON EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege', function($query) { $query->where('MIETVERTRAG_VON', '=', v); });
}
term(res) ::= MIETVERTRAG_BIS LIKE VALUE(v).
{
    res = Personen::whereHas('mietvertraege', function($query) { $query->where('MIETVERTRAG_BIS', 'like', '%' . v . '%'); });
}
term(res) ::= MIETVERTRAG_BIS EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege', function($query) { $query->where('MIETVERTRAG_BIS', '=', v); });
}
term(res) ::= MIETVERTRAG_LAUFZEIT EQ VALUE(v).
{
    res = Personen::whereHas('mietvertraege', function($query) {
        $query->where(function($query) {
            $query->where('MIETVERTRAG_VON', '<=', v)->where('MIETVERTRAG_BIS', '>=', v);
        })->orWhere(function($query) {
            $query->where('MIETVERTRAG_VON', '<=', v)->whereNull('MIETVERTRAG_BIS');
        });
    });
}
term(res) ::= KAUFVERTRAG_VON LIKE VALUE(v).
{
    res = Personen::whereHas('kaufvertraege', function($query) { $query->where('VON', 'like', '%' . v . '%'); });
}
term(res) ::= KAUFVERTRAG_VON EQ VALUE(v).
{
    res = Personen::whereHas('kaufvertraege', function($query) { $query->where('VON', '=', v); });
}
term(res) ::= KAUFVERTRAG_BIS LIKE VALUE(v).
{
    res = Personen::whereHas('kaufvertraege', function($query) { $query->where('BIS', 'like', '%' . v . '%'); });
}
term(res) ::= KAUFVERTRAG_BIS EQ VALUE(v).
{
    res = Personen::whereHas('kaufvertraege', function($query) { $query->where('BIS', '=', v); });
}
term(res) ::= KAUFVERTRAG_LAUFZEIT EQ VALUE(v).
{
    res = Personen::whereHas('kaufvertraege', function($query) {
        $query->where(function($query) {
            $query->where('VON', '<=', v)->where('BIS', '>=', v);
        })->orWhere(function($query) {
            $query->where('VON', '<=', v)->whereNull('BIS');
        });
    });
}