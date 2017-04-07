<div class="card card-expandable">
    <div class="card-content">
        <div class="card-title">{{$title}} ({{$audits->count()}})</div>
        <table class="striped">
            <thead>
            <th>Datum</th>
            <th>Ereignis</th>
            <th>Benutzer</th>
            <th>IP</th>
            <th>Änderungen</th>
            </thead>
            <tbody>
            @foreach($audits as $audit)
                <tr>
                    <td>
                        {{$audit->created_at}}
                    </td>
                    <td>
                        {{$audit->event}}
                    </td>
                    <td>
                        @if(!is_null($audit->user_id))
                            @include('shared.entities.benutzer', ['entity' => $audit->user])
                        @endif
                    </td>
                    <td>
                        {{$audit->ip_address}}
                    </td>
                    <td>
                        <ul style="margin: 0">
                            @foreach ($audit->getModified() as $attribute => $modified)
                                <li>@lang('person.'.$audit->event.'.modified.'.$attribute, $modified)</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>