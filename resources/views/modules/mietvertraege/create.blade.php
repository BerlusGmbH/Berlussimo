@extends('layouts.main-without-menu')

@section('breadcrumbs')
    <a href="{{ route('web::mietvertraege::legacy') }}" class="breadcrumb">Mietverträge</a>
    <a href="" class="breadcrumb">Neu</a>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="card-panel">
                <form action="{{route('web::mietvertraege::store')}}" method="post">
                    <div class="row">
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-account prefix"></i>
                            <div id="tenant-autocomplete" class="chips invalid" style="margin-left: 3rem">
                            </div>
                            <span class="error-block">{{$errors->has('tenants') ? $errors->first('tenants') : ''}}</span>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-cube-outline prefix"></i>
                            <input type="text" id="unit-autocomplete"
                                   class="autocomplete validate {{$errors->has('unit') ? 'invalid' : ''}}"
                                   name="unit_name"
                                   value="{{old('unit_name')}}" autocomplete="off">
                            <span class="error-block">{{$errors->has('unit') ? $errors->first('unit') : ''}}</span>
                            <label for="unit-autocomplete">Einheit</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-calendar-today prefix"></i>
                            <input type="date" class="datepicker {{$errors->has('move-in-date') ? 'invalid' : ''}}"
                                   id="move-in-date" name="move-in-date"
                                   value="{{old('move-in-date')}}">
                            <span class="error-block">{{$errors->has('move-in-date') ? $errors->first('move-in-date') : ''}}</span>
                            <label for="move-in-date">Einzugsdatum</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-calendar-range prefix"></i>
                            <input type="date" class="datepicker {{$errors->has('move-out-date') ? 'invalid' : ''}}"
                                   id="move-out-date" name="move-out-date"
                                   value="{{old('move-out-date')}}">
                            <span class="error-block">{{$errors->has('move-out-date') ? $errors->first('move-out-date') : ''}}</span>
                            <label for="move-out-date">Auszugsdatum</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-currency-eur prefix"></i>
                            <input type="number" step="0.01" min="0" id="rent" name="rent"
                                   class="validate {{$errors->has('rent') ? 'invalid' : ''}}"
                                   value="{{old('rent')}}">
                            <span class="error-block">{{$errors->has('rent') ? $errors->first('rent') : ''}}</span>
                            <label for="rent">Kaltmiete</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-security-home prefix"></i>
                            <input type="number" step="0.01" min="0" id="deposit" name="deposit"
                                   class="validate {{$errors->has('deposit') ? 'invalid' : ''}}"
                                   value="{{old('deposit')}}">
                            <span class="error-block">{{$errors->has('deposit') ? $errors->first('deposit') : ''}}</span>
                            <label for="deposit"
                                   data-error="{{ $errors->has('deposit') ? $errors->first('deposit') : '' }}">Sollkaution</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-delete prefix"></i>
                            <input type="number" step="0.01" min="0" id="bk-advance" name="bk-advance"
                                   class="validate {{ $errors->has('bk-advance') ? 'invalid' : '' }}"
                                   value="{{old('bk-advance')}}">
                            <span class="error-block">{{$errors->has('bk-advance') ? $errors->first('bk-advance') : ''}}</span>
                            <label for="bk-advance">Nebenkosten Vorauszahlung</label>
                        </div>
                        <div class="input-field col-xs-12 col-md-6">
                            <i class="mdi mdi-radiator prefix"></i>
                            <input type="number" step="0.01" min="0" id="hk-advance" name="hk-advance"
                                   class="validate {{$errors->has('hk-advance') ? 'invalid' : ''}}"
                                   value="{{old('hk-advance')}}">
                            <span class="error-block">{{$errors->has('hk-advance') ? $errors->first('hk-advance') : ''}}</span>
                            <label for="hk-advance">Heizkosten Vorauszahlung</label>
                        </div>
                        <div class="input-field col-xs-12 end-xs">
                            <button class="btn waves-effect waves-light" type="submit">Erfassen
                                <i class="mdi mdi-plus left"></i>
                            </button>
                        </div>
                        <input type="hidden" name="unit" id="unit" value="{{old('unit')}}">
                    </div>
                </form>
            </div>
        </div>
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
                            $end = date_create($unit['MIETVERTRAG_BIS']);
                            $posttag = '';
                            switch ($unit['TYP']) {
                                case 'Wohnraum' : $posttag .= '<i class="mdi mdi-home"></i> ';
                                    break;
                                case 'Gewerbe' : $posttag .= '<i class="mdi mdi-store"></i> ';
                                    break;
                                case 'Stellplatz' : $posttag .= '<i class="mdi mdi-car"></i> ';
                                    break;
                                default: $posttag .= ' (' . $unit['TYP'] . ')';
                            }
                            $posttag .= ($end > $now) ? '<i class="mdi mdi-briefcase"></i> ' . date_format($end, 'd.m.Y') : '';
                        @endphp
                '{!! $unit['EINHEIT_KURZNAME'] !!}': {
                    id: {!! $unit['EINHEIT_ID'] !!},
                    posttag: '{!! $posttag !!}',
                    icons: [{
                        icon: 'info',
                        link: '{!! route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => $unit['EINHEIT_ID']], false) !!}'
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
                '{!! trim($tenant['name']) !!}, {!! trim($tenant['first_name']) !!}': {
                    id: {{ $tenant['id'] }},
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