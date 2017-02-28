<div class="card card-expandable">
    <div class="card-content">
        <div class="card-title">Allgemeine Details ({{ $details->count() }})</div>
        <table class="striped">
            <thead>
            <th>Typ</th>
            <th>Wert</th>
            <th>Bemerkung</th>
            </thead>
            <tbody>
            @foreach( $details as $detail )
                <tr>
                    <td>
                        {{ $detail->DETAIL_NAME }}
                    </td>
                    <td>
                        {{ $detail->DETAIL_INHALT }}
                    </td>
                    <td>
                        {{ $detail->DETAIL_BEMERKUNG }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>