<div id="{{$id}}" class="modal">
    <form action="{{route('web::personen::update', ['person' => $person->id])}}" method="post">
        {{method_field('PATCH')}}
        <div class="modal-content">
            <h4>Person</h4>
            <div class="row">
                <div class="input-field col-xs-12">
                    <i class="mdi mdi-alphabetical prefix"></i>
                    <input type="text" minlength="1" maxlength="255" id="name" name="name"
                           class="validate {{$errors->has('name') ? 'invalid' : ''}}"
                           value="{{old('name', $person->name)}}">
                    <label for="name">Nachname</label>
                    <span class="error-block">{{$errors->has('name') ? $errors->first('name') : ''}}</span>
                </div>
                <div class="input-field col-xs-12">
                    <i class="mdi mdi-alphabetical prefix"></i>
                    <input type="text" minlength="1" maxlength="255" id="first_name" name="first_name"
                           class="validate {{$errors->has('first_name') ? 'invalid' : ''}}"
                           value="{{old('first_name', $person->first_name)}}">
                    <label for="first_name">Vorname</label>
                    <span class="error-block">{{$errors->has('first_name') ? $errors->first('first_name') : ''}}</span>
                </div>
                <div class="input-field col-xs-12">
                    <i class="mdi mdi-cake prefix"></i>
                    <input type="date" id="birthday" name="birthday"
                           class="validate {{$errors->has('birthday') ? 'invalid' : ''}}"
                           value="{{old('birthday', $person->birthday ? $person->birthday->toDateString() : '')}}">
                    <span class="error-block">{{$errors->has('birthday') ? $errors->first('birthday') : ''}}</span>
                    <label class="active" for="birthday" data-error="{{$errors->has('birthday')}}">Geburtstag</label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-action btn waves-effect waves-light red" type="submit">Ändern
                <i class="mdi mdi-pencil left"></i>
            </button>
            <a class="modal-close waves-effect btn-flat">Abbrechen</a>
        </div>
    </form>
</div>

@push('scripts')
<script type="text/javascript">
    @if($errors->has('name') || $errors->has('first_name') || $errors->has('birthday'))
        $('document').ready(function () {
        $('#{{$id}}').modal('open');
    });
    @endif
</script>
@endpush