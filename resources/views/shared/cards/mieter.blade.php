<div class="card card-expandable">
    <div class="card-content">
                        <span class="card-title"><a
                                    href="{{ route('web::personen::index', ['q' => '!person(mietvertrag(einheit(id=' . $einheit->EINHEIT_ID . ') laufzeit=' . \Carbon\Carbon::today()->toDateString() . '))']) }}">Mieter ({{ $einheit->mieter()->get()->count() }})
                            </a></span>
        <table class="striped">
            <thead>
            <th>Mieter</th>
            </thead>
            <tbody>
            @foreach( $mieter as $einMieter )
                <tr>
                    <td>
                        @include('shared.entities.person', [ 'entity' => $einMieter ])
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>