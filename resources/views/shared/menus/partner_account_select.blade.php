@if(check_user_links(Auth::user()->id, 'rechnungen'))
    @if(session()->has('partner_id'))
        <?php $p = new partners (); $p->get_partner_name(session()->get('partner_id')); ?>
        <a class="btn waves-effect primary-color text-variation-2"
           href='{{route('web::rechnungen::legacy', ['option' => 'partner_wechseln'])}}'>
            <i class="mdi mdi-account-multiple left"></i>
            <b>{{str_limit($p->partner_name, 20)}}</b></a>
    @else
        <a class="btn waves-effect primary-color text-variation-2"
           href='{{route('web::rechnungen::legacy', ['option' => 'partner_wechseln'])}}'>
            <i class="mdi mdi-account-multiple left"></i>
            Partner wählen</a>
    @endif
@endif
@if(check_user_links(Auth::user()->id, 'buchen' ))
    @if(session()->has('geldkonto_id'))
        <?php $g = new geldkonto_info(); $g->geld_konto_details(session()->get('geldkonto_id')); ?>
        <a class="btn waves-effect primary-color text-variation-2"
           href='{{route('web::buchen::legacy', ['option' => 'geldkonto_aendern'])}}'>
            <i class="mdi mdi-currency-eur left"></i>
            <b>{{str_limit($g->geldkonto_bezeichnung_kurz, 20)}}</b></a>
    @else
        <a class="btn waves-effect primary-color text-variation-2"
           href='{{route('web::buchen::legacy', ['option' => 'geldkonto_aendern'])}}'>
            <i class="mdi mdi-currency-eur left"></i>
            Geldkonto wählen</a>
    @endif
@endif