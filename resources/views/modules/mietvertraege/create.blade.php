@extends('layouts.main')

@section('submenu')
    <?php include(base_path('legacy/options/links/links.mietvertrag.php')); ?>
@endsection

@section('content')
    <div class="row">
        <form action="{{ route('legacy::mietvertraege::store') }}" method="post">
            <div class="input-field col s12 m6">
                <i class="material-icons prefix">people</i>
                <div id="tenant-autocomplete" class="chips invalid" style="margin-left: 3rem">
                </div>
                <span class="error-block">{{ $errors->has('tenants') ? $errors->first('tenants') : '' }}</span>
            </div>
            <div class="input-field col s12 m6">
                <i class="material-icons prefix">business</i>
                <input type="text" id="unit-autocomplete"
                       class="autocomplete validate {{ $errors->has('unit') ? 'invalid' : '' }}" name="unit_name"
                       value="{{ old('unit_name') }}" autocomplete="off">
                <span class="error-block">{{ $errors->has('unit') ? $errors->first('unit') : '' }}</span>
                <label for="unit-autocomplete">Einheit</label>
            </div>
            <div class="input-field col s12 m6">
                <i class="material-icons prefix">today</i>
                <input type="date" class="datepicker {{ $errors->has('move-in-date') ? 'invalid' : '' }}"
                       id="move-in-date" name="move-in-date"
                       value="{{ old('move-in-date') }}">
                <span class="error-block">{{ $errors->has('move-in-date') ? $errors->first('move-in-date') : '' }}</span>
                <label for="move-in-date">Einzugsdatum</label>
            </div>
            <div class="input-field col s12 m6">
                <i class="material-icons prefix">date_range</i>
                <input type="date" class="datepicker {{ $errors->has('move-out-date') ? 'invalid' : '' }}"
                       id="move-out-date" name="move-out-date"
                       value="{{ old('move-out-date') }}">
                <span class="error-block">{{ $errors->has('move-out-date') ? $errors->first('move-out-date') : '' }}</span>
                <label for="move-out-date">Auszugsdatum</label>
            </div>
            <div class="input-field col s12 m6">
                <i class="material-icons prefix">euro_symbol</i>
                <input type="number" step="0.01" min="0" id="rent" name="rent"
                       class="validate {{ $errors->has('rent') ? 'invalid' : '' }}"
                       value="{{ old('rent') }}">
                <span class="error-block">{{ $errors->has('rent') ? $errors->first('rent') : '' }}</span>
                <label for="rent">Kaltmiete</label>
            </div>
            <div class="input-field col s12 m6">
                <i class="material-icons prefix">euro_symbol</i>
                <input type="number" step="0.01" min="0" id="deposit" name="deposit"
                       class="validate {{ $errors->has('deposit') ? 'invalid' : '' }}"
                       value="{{ old('deposit') }}">
                <span class="error-block">{{ $errors->has('deposit') ? $errors->first('deposit') : '' }}</span>
                <label for="deposit" data-error="{{ $errors->has('deposit') ? $errors->first('deposit') : '' }}">Sollkaution</label>
            </div>
            <div class="input-field col s12 m6">
                <i class="material-icons prefix">euro_symbol</i>
                <input type="number" step="0.01" min="0" id="nk-advance" name="nk-advance"
                       class="validate {{ $errors->has('nk-advance') ? 'invalid' : '' }}"
                       value="{{ old('nk-advance') }}">
                <span class="error-block">{{ $errors->has('nk-advance') ? $errors->first('nk-advance') : '' }}</span>
                <label for="nk-advance">Nebenkosten Vorauszahlung</label>
            </div>
            <div class="input-field col s12 m6">
                <i class="material-icons prefix">euro_symbol</i>
                <input type="number" step="0.01" min="0" id="bk-advance" name="bk-advance"
                       class="validate {{ $errors->has('bk-advance') ? 'invalid' : '' }}"
                       value="{{ old('bk-advance') }}">
                <span class="error-block">{{ $errors->has('bk-advance') ? $errors->first('bk-advance') : '' }}</span>
                <label for="bk-advance">Betriebskosten Vorauszahlung</label>
            </div>
            <div class="input-field col s12">
                <button class="btn waves-effect waves-light" type="submit">Erfassen
                    <i class="material-icons right">send</i>
                </button>
            </div>
            <input type="hidden" name="unit" id="unit" value="{{ old('unit') }}">
        </form>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        //$('.chips-autocomplete').material_chip();
        var $unit_autocomplete = $('#unit-autocomplete');
        $unit_autocomplete.materialize_autocomplete({
            data: {
                @php($now = date_create())
                        @foreach($units as $unit)
                        @php
                            $end = date_create($unit->MIETVERTRAG_BIS);
                            $posttag = '';
                            switch ($unit->TYP) {
                                case 'Wohnraum' : $posttag .= '<i class="material-icons">home</i> ';
                                    break;
                                case 'Gewerbe' : $posttag .= '<i class="material-icons">business</i> ';
                                    break;
                                case 'Stellplatz' : $posttag .= '<i class="material-icons">directions_car</i> ';
                                    break;
                                default: $posttag .= ' (' . $unit->TYP . ')';
                            }
                            $posttag .= ($end > $now) ? '<i class="material-icons">work</i> ' . date_format($end, 'd.m.Y') : '';
                        @endphp
                '{!! $unit->EINHEIT_KURZNAME !!}': {
                    id: {!! $unit->EINHEIT_ID !!},
                    posttag: '{!! $posttag !!}',
                    icons: [{
                        icon: 'info',
                        link: '{!! route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => $unit->EINHEIT_ID], false) !!}'
                    }]
                },
                @endforeach
            }
        });
        $('.autocomplete-content').on('autocomplete.selected', function (e, key, data) {
            $('#unit').val(data[key].id);
        });
        $('#tenant-autocomplete').materialize_chips_autocomplete({
            placeholder: '+Mieter',
            secondaryPlaceholder: 'Mieter eingeben',
            data: [
                    @foreach(old('tenants') as $id => $tenant)
                {
                    id: {{ $id }},
                    tag: '{!! $tenant !!}'
                },
                @endforeach
            ],
            auto: {
                @foreach($tenants as $tenant)
                '{!! trim($tenant['PERSON_NACHNAME']) !!}, {!! trim($tenant['PERSON_VORNAME']) !!}': {
                    id: {{ $tenant['PERSON_ID'] }},
                    img: null
                },
                @endforeach
            }
        });
        $('.chips').on('chip.add', function (e, chip) {
            $('<input>').attr({
                type: 'hidden',
                id: 'tenant_' + chip.id,
                name: 'tenants[' + chip.id + ']'
            }).val(chip.tag).appendTo('form');
        }).on('chip.delete', function (e, chip) {
            $('#tenant_' + chip.id).remove();
        });
        @foreach(old('tenants') as $id => $tenant)
        $('<input>').attr({
            type: 'hidden',
            id: 'tenant_{{ $id }}',
            name: 'tenants[{{ $id }}]'
        }).val('{!! $tenant !!}').appendTo('form');
        @endforeach
    });
</script>
@endpush