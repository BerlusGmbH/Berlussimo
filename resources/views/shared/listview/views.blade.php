<select id='{{$id}}' name='{{$name}}'>
    @foreach($options as $option)
        <option value="{{$option}}" {{request()->input($name) == $option ? 'selected' : ''}}>{{$option}}</option>
    @endforeach
</select>
<label>{{$label}}</label>