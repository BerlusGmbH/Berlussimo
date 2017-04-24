<div class="card card-expandable">
    <div class="card-content">
        <div class="card-title">{{$title}} ({{$jobs->count()}})</div>
        <table class="striped responsive-table">
            <thead>
            <th>Titel</th>
            <th>Arbeitgeber</th>
            <th>Eintritt</th>
            <th>Austritt</th>
            <th>Wochenstunden</th>
            <th>Urlaubstage</th>
            <th>Stundensatz</th>
            <th>Bearbeiten</th>
            </thead>
            <tbody>
            @foreach($jobs as $job)
                <tr>
                    <td>
                        {{$job->title->title}}
                    </td>
                    <td>
                        @include('shared.entities.partner', ['entity' => $job->employer])
                    </td>
                    <td>
                        {{$job->join_date}}
                    </td>
                    <td>
                        {{$job->leave_date}}
                    </td>
                    <td>
                        {{$job->hours_per_week}}
                    </td>
                    <td>
                        {{$job->holidays}}
                    </td>
                    <td>
                        {{$job->hourly_rate}}
                    </td>
                    <td>
                        <a href="#job_edit_{{$job->id}}"><i class="mdi mdi-pencil"></i></a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @foreach($jobs as $job)
            @include('modules.personen.jobs.edit', ['id' => 'job_edit_' . $job->id])
        @endforeach
    </div>
</div>