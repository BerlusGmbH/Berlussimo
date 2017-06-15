<div class="card card-expandable">
    <div class="card-content">
        <span class="card-title">
            <a href="{{ $href }}">
                {{$title}} ({{$eigentuemer->count()}})
            </a>
        </span>
        <table class="striped">
            <thead>
            <th>Eigentümer</th>
            </thead>
            <tbody>
            @foreach( $eigentuemer as $einEigentuemer )
                <tr>
                    <td>
                        @include('shared.entities.person', [ 'entity' => $einEigentuemer ])
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>