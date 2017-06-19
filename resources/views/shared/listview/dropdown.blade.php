@php
    if(!isset($multiple)) {
        $multiple = false;
    }
@endphp
@if($multiple)
    <select class="listview-filter-multiple" id='{{$id}}' name='{{$name}}[]' multiple>
        <option value="" disabled selected>Bitte wählen</option>
@else
            <select class="listview-filter" id='{{$id}}' name='{{$name}}'>
@endif
                @foreach($options as $option)
                    @if(!$multiple)
                        @php($name = explode('[', $name)[0])
                        <option value="{{$option}}" {{$option == request()->input($name) ? 'selected' : ''}}>{{$option}}</option>
                    @else
                        <option value="{{$option}}" {{in_array($option, request()->input($name)) ? 'selected' : ''}}>{{$option}}</option>
                    @endif
                @endforeach
            </select>
            <label for='{{$id}}'>{{$label}}</label>