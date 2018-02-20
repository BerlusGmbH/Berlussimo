<div class="card card-expandable">
    <div class="card-content">
        <div class="card-title">{{$title}} ({{$roles->count()}})</div>
        <table class="striped responsive-table">
            <thead>
            <th>Rolle</th>
            </thead>
            <tbody>
            @foreach($roles as $role)
                <tr>
                    <td>
                        {{$role->name}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>