@extends('layouts.main-without-menu')

@section('breadcrumbs')
    <a href="{{ route('web::benutzer::index') }}" class="breadcrumb">Mitarbeiter</a>
@endsection

@section('content')
    <div class="card-panel white">
        <form id="filter-form" method="get">
            <div class="row">
                <div class="input-field col-xs-6 col-md-1">
                    <a class='waves-effect waves-light btn tooltipped' data-position="bottom" data-delay="50" data-tooltip="Auftrag an Mitarbeiter"
                       href='{{ route('web::benutzer::legacy', ['option' => 'neuer_benutzer']) }}'>
                        <i class="mdi mdi-plus"></i><i class="mdi mdi-worker"></i></a>
                </div>
                <div class="input-field col-xs-12 col-md-8">
                    <i class="mdi mdi-filter-variant prefix"></i>
                    <input id="query" name="q" value="{{ request()->input('q') }}" type="text"
                           autocomplete="off">
                    <label for="query">Abfrage</label>
                </div>
                <div class="input-field col-xs-12 col-md-2">
                    @php($options = [
                        '(ohne)' => '""',
                        'Mitarbeiterliste' => 'mitarbeiter !mitarbeiter[name:asc] mitarbeiter[id] mitarbeiter[email] mitarbeiter[geburtstag] gewerk mitarbeiter[von bis] mitarbeiter[stundensatz] mitarbeiter[wochenstunden] mitarbeiter[urlaubstage] partner'
                    ])
                    @include('shared.listview.views', ['id' => 'view', 'name' => 'v', 'label' => 'Ansicht', 'options' => $options])
                </div>
                <div class="input-field col-xs-6 col-md-1">
                    @include('shared.listview.resultsize', ['name' => 's', 'id' => 'size', 'label' => 'Anzahl'])
                </div>
                <div class="input-field col-md-2 col-md-offset-8">
                    @php($options = [
                        '(nicht gesetzt)' => '""',
                        'Ja' => '!mitarbeiter(aktiv)',
                        'Nein' => '!mitarbeiter(!aktiv)'
                    ])
                    @include('shared.listview.filters', ['name' => 'f[1]', 'id' => 'filter1', 'label' => 'Aktiv', 'multiple' => false, 'options' => $options])
                </div>
                <div class="input-field col-md-2">
                    @php($options = [
                        '(nicht gesetzt)' => ''
                    ])
                    @foreach($arbeitgeber as $einArbeitgeber)
                        @php($options = array_add($options, str_limit($einArbeitgeber->PARTNER_NAME, 40), '!mitarbeiter(partner(id=' . $einArbeitgeber->PARTNER_ID . '))'))
                    @endforeach
                    @include('shared.listview.filters', ['name' => 'f[2]', 'id' => 'filter2', 'label' => 'Arbeitgeber', 'multiple' => false, 'options' => $options])
                </div>
            </div>
        </form>
        @include('shared.tables.entities-with-paginator', ['parameters' => ['q', 's', 'v', 'f'] ,'columns' => $columns, 'entities' => $entities, 'class' => \App\Models\Person::class])
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {

        var submit = function (target) {
            target.form.submit();
        };

        $('#query').keypress(function (e) {
            if (e.which == KeyCode.KEY_ENTER || e.which == KeyCode.KEY_RETURN) {
                submit(this);
            }
        });
        $('#size').on('change', function () {
            submit(this);
        });
        $('#view').on('change', function () {
            submit(this);
        });
        var $filter = $('select.listview-filter');
        $filter.each(function () {
            $(this).on('change', function () {
                submit(this);
            });
        });
    });
</script>
@endpush