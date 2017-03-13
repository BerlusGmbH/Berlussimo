<div class="card card-expandable">
    <div class="card-content">
        <span class="card-title">
            <a href="{{$href}}">
                {{$title}} ({{$einheiten->count()}})
            </a>
        </span>
        <table class="striped">
            <thead>
            <th>Einheit</th>
            </thead>
            <tbody>
            @foreach( $einheiten as $einheit )
                <tr>
                    <td>
                        @include('shared.entities.einheit', [ 'entity' => $einheit])
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>