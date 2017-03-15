@if($multiple)
    <select class="listview-filter" id='{{$id}}' name='{{$name}}[]' multiple>
@else
    <select class="listview-filter" id='{{$id}}' name='{{$name}}'>
@endif
        <option value="" disabled selected>Bitte wählen</option>
        @foreach($options as $option => $value)
            @if(!$multiple)
                @php($name = explode('[', $name)[0])
            @endif
            <option value="{{$value}}" {{in_array($value, request()->input($name)) ? 'selected' : ''}}>{{$option}}</option>
        @endforeach
    </select>
    <label for='{{$id}}'>{{$label}}</label>