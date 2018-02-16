@can(\App\Libraries\Permission::PERMISSION_MODUL_RECHNUNG)
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
@endcan