<form>
    <div class="input-field">
        <input id="searchbar" type="search" required>
        <label for="searchbar"><i class="material-icons">search</i></label>
        <i class="material-icons">close</i>
    </div>
</form>

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $('#searchbar').searchbar();
    });
</script>
@endpush