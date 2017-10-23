<div class="card card-expandable">
    <div class="card-content">
        <span class="card-title">
            <div class="row">
                <div class="col-xs-12 col-sm-8">
                    <a href="{{$href}}">
                        {{$title}} ({{$auftraege->count()}})
                    </a>
                </div>
                <div class="col-xs-12 col-sm-4 end-xs">
                    @if($hasHinweis)
                        <i class="mdi mdi-alert red-text tooltipped"
                       data-tooltip="Hinweise beachten"></i>
                    @endif
                    <a class='waves-effect waves-light btn tooltipped'
                       data-tooltip="Auftrag an Mitarbeiter"
                       href='{{ route('web::construction::legacy', ['option' => 'neues_projekt', 'typ' => 'Benutzer', 'kos_typ' => $type, 'kos_id' => $id]) }}'>
                        <i class="mdi mdi-plus"></i><i class="mdi mdi-clipboard"></i><i class="mdi mdi-worker"></i></a>
                    <a class='waves-effect waves-light btn tooltipped'
                       data-tooltip="Auftrag an Partner"
                       href='{{ route('web::construction::legacy', ['option' => 'neues_projekt', 'typ' => 'Partner', 'kos_typ' => $type, 'kos_id' => $id]) }}'>
                        <i class="mdi mdi-plus"></i><i class="mdi mdi-clipboard"></i><i
                                class="mdi mdi-account-multiple"></i></a>
                </div>
            </div>
        </span>
        @if(!$auftraege->isEmpty())
            <table class="striped responsive-table">
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
                        <td style="white-space: normal">
                            {{  $auftrag->TEXT }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>