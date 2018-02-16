<div class="card card-expandable">
    <div class="card-content">
        <div class="card-title">{{$title}} ({{$hinweise->count()}})</div>
        <table class="striped">
            <thead>
            <th>Wert</th>
            <th>Bemerkung</th>
            </thead>
            <tbody>
            @foreach( $hinweise as $detail )
                <tr>
                    <td>
                        {{$detail->DETAIL_INHALT}}
                    </td>
                    <td>
                        {{$detail->DETAIL_BEMERKUNG}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>