<div class="card card-expandable">
    <div class="card-content">
        <span class="card-title">
            <a href="{{ $href }}">
                {{$title}} ({{$mieter->count()}})
            </a>
        </span>
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