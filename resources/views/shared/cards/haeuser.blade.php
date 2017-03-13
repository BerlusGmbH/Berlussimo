<div class="card card-expandable">
    <div class="card-content">
        <span class="card-title">
            <a href="{{$href}}">
                {{$title}} ({{ $objekt->haeuser->count() }})
            </a>
        </span>
        <table class="striped">
            <thead>
            <th>Haus</th>
            </thead>
            <tbody>
            @foreach( $haeuser as $haus )
                <tr>
                    <td>
                        @include('shared.entities.haus', [ 'entity' => $haus])
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>