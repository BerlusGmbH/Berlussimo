<select id='{{$id}}' name='{{$name}}[]' multiple>
    <option value="" disabled selected>Bitte wählen</option>
    @foreach($options as $option => $value)
        <option value="{{$value}}" {{in_array($value, request()->input($name)) ? 'selected' : ''}}>{{$option}}</option>
    @endforeach
</select>
<label>{{$label}}</label>