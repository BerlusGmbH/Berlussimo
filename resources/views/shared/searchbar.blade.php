<form>
    <div class="input-field">
        <input id="searchbar" type="search" required autocomplete="off">
        <label for="searchbar"><i class="mdi mdi-magnify"></i></label>
        <i id="searchbarClose" class="mdi mdi-close"></i>
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
            objekturl: '{{ route('web::objekte::show', ['id' => '']) }}/',
            objektlisturl: '{{ route('web::objekte::index', ['q' => '']) }}',
            hausurl: '{{ route('web::haeuser::show', ['id' => '']) }}/',
            hauslisturl: '{{ route('web::haeuser::index', ['q' => '']) }}',
            einheiturl: '{{ route('web::einheiten::show', ['id' => '']) }}/',
            einheitlisturl: '{{ route('web::einheiten::index', ['q' => '']) }}',
            personurl: '{{ route('web::personen::show', ['id' => '']) }}/',
            personlisturl: '{{ route('web::personen::index', ['q' => '']) }}',
            partnerurl: '{{ route('web::partner::legacy', ['option' => 'partner_im_detail', 'partner_id' => '']) }}',
            partnerlisturl: '/'
        });
    });
</script>
@endpush