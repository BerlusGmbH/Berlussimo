<form>
    <div class="input-field">
        <input id="searchbar" type="search" required autocomplete="off">
        <label for="searchbar"><i class="material-icons">search</i></label>
        <i id="searchbarClose" class="material-icons">close</i>
        <div id="searchbarIndicator" class="preloader-wrapper small active" style="position: absolute; top: 14px; right: 0.8rem; display: none">
            <div class="spinner-layer spinner-gray-only">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div><div class="gap-patch">
                    <div class="circle"></div>
                </div><div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $('#searchbar').searchbar({
            loginurl: '{{ url('/login') }}',
            objekturl: '{{ route('web::haeuser::legacy', ['haus_raus' => 'haus_kurz', 'objekt_id' => '']) }}',
            objektlisturl: '/',
            hausurl: '{{ route('web::einheiten::legacy', ['einheit_raus' => 'einheit_kurz', 'haus_id' => '']) }}',
            hauslisturl: '/',
            einheiturl: '{{ route('web::uebersicht::legacy', ['anzeigen' => 'einheit', 'einheit_id' => '']) }}',
            einheitlisturl: '/',
            personurl: '{{ route('web::personen::show', ['id' => '']) }}/',
            personlisturl: '{{ route('web::personen::index', ['q' => '']) }}',
            partnerurl: '{{ route('web::partner::legacy', ['option' => 'partner_im_detail', 'partner_id' => '']) }}',
            partnerlisturl: '/'
        });
    });
</script>
@endpush