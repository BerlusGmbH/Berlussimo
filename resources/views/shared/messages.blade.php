@foreach(session('errors') as $error)
    <div class="chip red red-text text-darken-4 lighten-3">
        <i class="img material-icons">error</i>
        {{$error}}
        <i class="close material-icons">close</i>
    </div>
@endforeach
@foreach(session('infos') as $info)
    <div class="chip blue blue-text text-darken-4 lighten-3">
        <i class="img material-icons">info</i>
        {{$info}}
        <i class="close material-icons">close</i>
    </div>
@endforeach
@foreach(session('warinings') as $warning)
    <div class="chip yellow yellow-text text-darken-4 lighten-3">
        <i class="img material-icons">warning</i>
        {{$warning}}
        <i class="close material-icons">close</i>
    </div>
@endforeach