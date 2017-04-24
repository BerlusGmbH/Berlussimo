<div id="{{$id}}" class="modal">
    <form action="{{route('web::persons.credentials.store', ['person' => $person])}}" method="post">
        <div class="modal-content">
            <h4>Benutzer</h4>
            <h5>Anmeldedaten</h5>
            <div class="row">
                <div class="input-field col-xs-12">
                    <i class="mdi mdi-textbox prefix"></i>
                    <input type="password" minlength="1" maxlength="255" id="password" name="password"
                           class="validate {{$errors->has('password') ? 'invalid' : ''}}"
                           value="{{old('password')}}">
                    <label for="name">Passwort</label>
                    <span class="error-block">{{$errors->has('password') ? $errors->first('password') : ''}}</span>
                </div>
            </div>
            <h5>Rollen</h5>
            <div class="row">
                @foreach(\Spatie\Permission\Models\Role::all() as $key => $role)
                    <div class="input-field col-xs-12 col-sm-6 col-md-4 col-lg-3">
                        <input class="filled-in" type="checkbox" id="role_{{$key}}" name="roles[]"
                               value="{{$role->name}}" {{in_array($role->name, old('roles')) ? 'checked' : ''}}>
                        <label for="role_{{$key}}">{{$role->name}}</label>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn waves-effect waves-light red" type="submit"><i class="mdi mdi-plus"></i>Hinzufügen
            </button>
            <a class="modal-action modal-close waves-effect btn-flat">Abbrechen</a>
        </div>
    </form>
</div>

@push('scripts')
<script type="text/javascript">
    @if($errors->has('password'))
        $('document').ready(function () {
        $('#{{$id}}').modal('open');
    });
    @endif
</script>
@endpush