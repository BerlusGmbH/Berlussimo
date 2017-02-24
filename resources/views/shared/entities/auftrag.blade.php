<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
        $tooltip = 'Auftrag';
        if($entity->ERLEDIGT == '1' && $entity->AKUT == 'JA') {
            $tooltip .= ' (Erledigt, Akut)';
        } elseif ($entity->ERLEDIGT == '0' && $entity->AKUT == 'JA') {
            $tooltip .= ' (Offen, Akut)';
        } elseif ($entity->ERLEDIGT == '1') {
            $tooltip .= ' (Erledigt)';
        } elseif($entity->ERLEDIGT == '0') {
            $tooltip .= ' (Offen)';
        }
    @endphp
    @if($icons)
        @if($entity->ERLEDIGT == '1')
            <i class="mdi mdi-clipboard-check tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{ $tooltip }}"></i>
        @elseif($entity->AKUT == 'JA')
            <i class="mdi mdi-clipboard-alert tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{ $tooltip }}"></i>
        @else
            <i class="mdi mdi-clipboard tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{ $tooltip }}"></i>
        @endif
    @endif
    <a href="{{ route('web::todo::legacy', ['option' => 'edit', 't_id' => $entity->T_ID]) }}" class="tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{ trim($entity->TEXT) }}">T-{{ $entity->T_ID }}</a>
    <a target="_blank" href="{{ route('web::todo::legacy', ['option' => 'pdf_auftrag', 'proj_id' => $entity->T_ID]) }}" class="tooltipped" data-position="bottom" data-delay="50" data-tooltip="PDF"><i class="mdi mdi-file-pdf"></i></a>
</span>