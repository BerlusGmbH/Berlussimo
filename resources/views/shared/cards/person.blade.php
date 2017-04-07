<div class="card">
    <div class="card-content">
        <div class="card-title">
            @if(!is_null($person->birthday))
                @php($margin_bot = '0px')
            @else
                @php($margin_bot = '12px')
            @endif
            <div class="row" style="line-height: 24px; margin-bottom: {{$margin_bot}}; margin-top: 12px">
                <div class="col-xs-12 col-sm-8">
                    @include('shared.entities.person', ['entity' => $person])
                    @if(!is_null($person->birthday))
                        <div style="font-size: small; line-height: 24px; margin-left: 6px">
                            <i class="mdi mdi-star"></i> {{ $person->birthday->formatLocalized("%d.%m.%Y") }}
                        </div>
                    @endif
                </div>
                <div class="col-xs-12 col-sm-4 end-xs">
                    <a href="{{route('web::personen::edit', ['id' => $person->id])}}"><i
                                class="mdi mdi-pencil"></i></a>
                    <a href="{{route('web::details::legacy', ['option' => 'details_hinzu', 'detail_tabelle' => 'PERSON', 'detail_id' => $person->id])}}"><i
                                class="mdi mdi-table-edit"></i></a>
                </div>
            </div>
        </div>
        <div class="row">
            @inject('phonelocator', 'App\Services\PhoneLocator')
            @foreach($person->phones as $phone)
                <div class="col-xs-12 col-sm-6">
                    @include('shared.entities.details.telefon', ['entity' => $phone])
                </div>
            @endforeach
            @foreach($person->emails as $email)
                <div class="col-xs-12 col-sm-6">
                    @include('shared.entities.details.email', ['entity' => $email])
                </div>
            @endforeach
            @foreach($person->faxs as $fax)
                <div class="col-xs-12 col-sm-6">
                    @include('shared.entities.details.fax', ['entity' => $fax])
                </div>
            @endforeach
            @foreach($person->adressen as $anschrift)
                <div class="col-xs-12 col-sm-6">
                    @include('shared.entities.details.adresse', ['entity' => $anschrift])
                </div>
            @endforeach
        </div>
    </div>
</div>