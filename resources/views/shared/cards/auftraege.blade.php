<div class="card card-expandable">
    <div class="card-content">
        <span class="card-title">
            <div class="row">
                <div class="col-xs-8">
                    Aufträge ({{ $auftraege->count() }})
                </div>
                <div class="col-xs-4 end-xs">
                    <a class='waves-effect waves-light btn tooltipped' data-position="bottom" data-delay="50" data-tooltip="Auftrag an Mitarbeiter"
                       href='{{ route('web::todo::legacy', ['option' => 'neues_projekt', 'typ' => $type, 'kos_typ' => 'Objekt', 'kos_id' => $objekt->OBJEKT_ID]) }}'><i
                                class="mdi mdi-clipboard"></i><i class="mdi mdi-worker"></i></a>
                    <a class='waves-effect waves-light btn tooltipped' data-position="bottom" data-delay="50" data-tooltip="Auftrag an Partner"
                       href='{{ route('web::todo::legacy', ['option' => 'neues_projekt', 'typ' => $type, 'kos_typ' => 'Objekt', 'kos_id' => $objekt->OBJEKT_ID]) }}'><i
                                class="mdi mdi-clipboard"></i><i class="mdi mdi-account-multiple"></i></a>
                </div>
            </div>
        </span>
        @if(!$auftraege->isEmpty())
            <table class="striped">
                <thead>
                <th>Auftrag</th>
                <th>Erstellt</th>
                <th>Von</th>
                <th>An</th>
                <th>Text</th>
                </thead>
                <tbody>
                @foreach( $auftraege as $auftrag )
                    <tr>
                        <td>
                            @include('shared.entities.auftrag', [ 'entity' => $auftrag ])
                        </td>
                        <td>
                            {{  $auftrag->ERSTELLT }}
                        </td>
                        <td>
                            @include('shared.entity', [ 'entity' => $auftrag->von ])
                        </td>
                        <td>
                            @include('shared.entity', [ 'entity' => $auftrag->an ])
                        </td>
                        <td>
                            {{  $auftrag->TEXT }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>