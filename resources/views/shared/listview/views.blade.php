<select id='{{$id}}' name='{{$name}}'>
    @foreach($options as $option => $value)
        <option value="{{$value}}" {{request()->input($name) == $value ? 'selected' : ''}}>{{$option}}</option>
    @endforeach
</select>
<label>{{$label}}</label>