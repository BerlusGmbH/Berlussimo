@if(!is_null(session(\App\Messages\ErrorMessage::TYPE)))
    @foreach(session(\App\Messages\ErrorMessage::TYPE) as $error)
        <div class="chip red red-text text-darken-4 lighten-3">
            <i class="img mdi mdi-alert-circle"></i>
            {{$error}}
            <i class="close mdi mdi-close"></i>
        </div>
    @endforeach
@endif
@if(!is_null(session(\App\Messages\WarningMessage::TYPE)))
    @foreach(session(\App\Messages\WarningMessage::TYPE) as $warning)
        <div class="chip yellow yellow-text text-darken-4 lighten-3">
            <i class="img mdi-alert"></i>
            {{$warning}}
            <i class="close mdi mdi-close"></i>
        </div>
    @endforeach
@endif
@if(!is_null(session(\App\Messages\InfoMessage::TYPE)))
    @foreach(session(\App\Messages\InfoMessage::TYPE) as $info)
        <div class="chip blue blue-text text-darken-4 lighten-3">
            <i class="img mdi mdi-information"></i>
            {{$info}}
            <i class="close mdi mdi-close"></i>
        </div>
    @endforeach
@endif