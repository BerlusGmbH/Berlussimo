<div class="card card-expandable">
    <div class="card-content">
        <span class="card-title">Mietverträge ({{ $mietvertraege->count() }})</span>
        <table class="striped">
            <thead>
            <th>Mietvertrag</th>
            <th>Von</th>
            <th>Bis</th>
            </thead>
            <tbody>
            @foreach( $mietvertraege as $mietvertrag)
                <tr>
                    <td>
                        @include('shared.entities.mietvertrag', [ 'entity' => $mietvertrag ])
                    </td>
                    <td>
                        {{ $mietvertrag->MIETVERTRAG_VON }}
                    </td>
                    <td>
                        {{ $mietvertrag->MIETVERTRAG_BIS }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>