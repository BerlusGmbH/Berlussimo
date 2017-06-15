<div class="card card-expandable">
    <div class="card-content">
        <span class="card-title">Kaufverträge ({{ $kaufvertraege->count() }})</span>
        <table class="striped responsive-table">
            <thead>
            <th>Kaufvertrag</th>
            <th>Von</th>
            <th>Bis</th>
            </thead>
            <tbody>
            @foreach( $kaufvertraege as $kaufvertrag)
                <tr>
                    <td>
                        @include('shared.entities.kaufvertrag', [ 'entity' => $kaufvertrag ])
                    </td>
                    <td>
                        {{ $kaufvertrag->VON }}
                    </td>
                    <td>
                        {{ $kaufvertrag->BIS }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>