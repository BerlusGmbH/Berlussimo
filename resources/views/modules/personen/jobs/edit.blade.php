<div id="{{$id}}" class="modal">
    <form action="{{route('web::persons.jobs.update', ['person' => $person, 'job' => $job])}}" method="post">
        {{method_field('PATCH')}}
        <div class="modal-content">
            <h4>Arbeitnehmer</h4>
            <div class="row">
                <div class="input-field col s12">
                    <i class="mdi mdi-account-multiple prefix"></i>
                    <input type="text" id="{{$id}}_employer" name="employer" value="{{$job->employer->PARTNER_NAME}}"
                           autocomplete="off" readonly>
                    <label for="{{$id}}_employer">Arbeitgeber</label>
                </div>
                <div class="input-field col s12">
                    <i class="mdi mdi-book-open-page-variant prefix"></i>
                    <input type="text" id="{{$id}}_title" name="title" value="{{old('title', $job->title->title)}}"
                           class="autocomplete validate {{$errors->has('title') ? 'invalid' : ''}}"
                           autocomplete="off">
                    <span class="error-block">{{$errors->has('title') ? $errors->first('title') : ''}}</span>
                    <label for="{{$id}}_title">Titel</label>
                </div>
                <div class="input-field col-xs-12 col-md-6">
                    <i class="mdi mdi-calendar-today prefix"></i>
                    <input type="date" id="{{$id}}_join_date" name="join_date"
                           class="validate {{$errors->has('join_date') ? 'invalid' : ''}}"
                           value="{{old('join_date', $job->join_date)}}">
                    <label class="active" for="{{$id}}_join_date">Eintritt</label>
                    <span class="error-block">{{$errors->has('join_date') ? $errors->first('join_date') : ''}}</span>
                </div>
                <div class="input-field col-xs-12 col-md-6">
                    <i class="mdi mdi-calendar-range prefix"></i>
                    <input type="date" id="{{$id}}_leave_date" name="leave_date"
                           class="validate {{$errors->has('leave_date') ? 'invalid' : ''}}"
                           value="{{old('leave_date', $job->leave_date)}}">
                    <label class="active" for="{{$id}}_leave_date">Austritt</label>
                    <span class="error-block">{{$errors->has('leave_date') ? $errors->first('leave_date') : ''}}</span>
                </div>
                <div class="input-field col-xs-12 col-md-4">
                    <i class="mdi mdi-clock prefix"></i>
                    <input type="number" id="{{$id}}_hours_per_week" name="hours_per_week"
                           min="0" max="168" step="0.5"
                           class="validate {{$errors->has('hours_per_week') ? 'invalid' : ''}}"
                           value="{{old('hours_per_week', $job->hours_per_week)}}">
                    <label for="{{$id}}_hours_per_week">Wochenstunden</label>
                    <span class="error-block">{{$errors->has('hours_per_week') ? $errors->first('hours_per_week') : ''}}</span>
                </div>
                <div class="input-field col-xs-12 col-md-4">
                    <i class="mdi mdi-white-balance-sunny prefix"></i>
                    <input type="number" id="{{$id}}_holidays" name="holidays"
                           min="0" max="365" step="0.5"
                           class="validate {{$errors->has('holidays') ? 'invalid' : ''}}"
                           value="{{old('holidays', $job->holidays)}}">
                    <label for="{{$id}}_holidays">Urlaubstage</label>
                    <span class="error-block">{{$errors->has('holidays') ? $errors->first('holidays') : ''}}</span>
                </div>
                <div class="input-field col-xs-12 col-md-4">
                    <i class="mdi mdi-currency-eur prefix"></i>
                    <input type="number" id="{{$id}}_hourly_rate" name="hourly_rate"
                           min="0" max="1000" step="0.01"
                           class="validate {{$errors->has('hourly_rate') ? 'invalid' : ''}}"
                           value="{{old('hourly_rate', $job->hourly_rate)}}">
                    <label for="{{$id}}_hourly_rate">Stundensatz</label>
                    <span class="error-block">{{$errors->has('hourly_rate') ? $errors->first('hourly_rate') : ''}}</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn waves-effect waves-light red" type="submit"><i class="mdi mdi-pencil"></i>Ändern
            </button>
            <a class="modal-action modal-close waves-effect btn-flat">Abbrechen</a>
        </div>
    </form>
</div>

@push('scripts')
<script type="text/javascript">
    $('document').ready(function () {
        @if(session()->exists($id))
            $('#{{$id}}').modal('open');
        @endif
        $('#{{$id}}_title').autocomplete({
            data: {
        @foreach(\App\Models\JobTitle::defaultOrder()->get(['title']) as $title)
        {!! json_encode(trim($title->title)) !!} :
        null,
        @endforeach
    }
    })
        ;
    });
</script>
@endpush