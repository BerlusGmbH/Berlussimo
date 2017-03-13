<select id="{{$id}}" name="{{$name}}">
    <option value="5" {{request()->input($name) == 5 ? 'selected' : ''}}>5</option>
    <option value="10" {{request()->input($name) == 10 ? 'selected' : ''}}>10</option>
    <option value="20" {{request()->input($name) == 20 ? 'selected' : ''}}>20</option>
    <option value="50" {{request()->input($name) == 50 ? 'selected' : '' }}>50</option>
    <option value="100" {{request()->input($name) == 100 ? 'selected' : ''}}>100</option>
</select>
<label>{{$label}}</label>