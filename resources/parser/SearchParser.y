/* This is an example for a Parser in PHP */

%declare_class {class SearchParser}
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

start(res) ::= expression(e).

expression ::= expression term.
expression ::= term.
term ::= PERSON_NAME EQ VALUE(v).
{
    $this->query->where('PERSON_NACHNAME', '=', v);
}
term ::= PERSON_NAME LIKE VALUE(v).
{
    $this->query->where('PERSON_NACHNAME', 'like', '%' . v . '%');
}
term ::= PERSON_VORNAME EQ VALUE(v).
{
    $this->query->where('PERSON_VORNAME', '=', v);
}
term ::= PERSON_VORNAME LIKE VALUE(v).
{
    $this->query->where('PERSON_VORNAME', 'like', '%' . v . '%');
}
term ::= PERSON_ID EQ VALUE(v).
{
    $this->query->where('PERSON_ID', '=', v);
}
term ::= PERSON_ID LIKE VALUE(v).
{
    $this->query->where('PERSON_ID', 'like', '%' . v . '%');
}
term ::= EINHEIT_NAME EQ VALUE(v).
{
    SearchParserWrapper::einheit_name_eq_value($this->query, v);
}
term ::= EINHEIT_NAME LIKE VALUE(v).
{
    SearchParserWrapper::einheit_name_like_value($this->query, v);
}
term ::= EINHEIT_LAGE EQ VALUE(v).
{
    SearchParserWrapper::einheit_lage_eq_value($this->query, v);
}
term ::= EINHEIT_LAGE LIKE VALUE(v).
{
    SearchParserWrapper::einheit_lage_like_value($this->query, v);
}
term ::= EINHEIT_ID EQ VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit', function($query) { $query->where('EINHEIT_ID', '=', v); });
}
term ::= EINHEIT_ID LIKE VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit', function($query) { $query->where('EINHEIT_ID', 'like', '%' . v . '%'); });
}
term ::= HAUS_STRASSE EQ VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_STRASSE', '=', v); });
}
term ::= HAUS_STRASSE LIKE VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_STRASSE', 'like', '%' . v . '%'); });
}
term ::= HAUS_NUMMER EQ VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_NUMMER', '=', v); });
}
term ::= HAUS_NUMMER LIKE VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_NUMMER', 'like', '%' . v . '%'); });
}
term ::= HAUS_PLZ EQ VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_PLZ', '=', v); });
}
term ::= HAUS_PLZ LIKE VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_PLZ', 'like', '%' . v . '%'); });
}
term ::= HAUS_ORT EQ VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_ORT', '=', v); });
}
term ::= HAUS_ORT LIKE VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_ORT', 'like', '%' . v . '%'); });
}
term ::= HAUS_ID EQ VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_ID', '=', v); });
}
term ::= HAUS_ID LIKE VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus', function($query) { $query->where('HAUS_ID', 'like', '%' . v . '%'); });
}
term ::= OBJEKT_NAME EQ VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus.objekt', function($query) { $query->where('OBJEKT_KURZNAME', '=', v); });
}
term ::= OBJEKT_NAME LIKE VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus.objekt', function($query) { $query->where('OBJEKT_KURZNAME', 'like', '%' . v . '%'); });
}
term ::= OBJEKT_ID EQ VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus.objekt', function($query) { $query->where('OBJEKT_ID', '=', v); });
}
term ::= OBJEKT_ID LIKE VALUE(v).
{
    $this->query->whereHas('mietvertraege.einheit.haus.objekt', function($query) { $query->where('OBJEKT_ID', 'like', '%' . v . '%'); });
}
