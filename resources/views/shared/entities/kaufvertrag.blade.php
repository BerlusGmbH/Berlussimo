<span style="white-space: nowrap">
    <i class="mdi mdi-checkbox-blank-circle tooltipped" data-position="bottom" data-delay="50" data-tooltip="Kaufvertrag"></i>
        <a href="{{ route('web::weg::legacy', ['option' => 'einheit_uebersicht', 'einheit_id' => $entity->EINHEIT_ID]) }}">KV-{{ $entity->ID }}</a>
        <i class="mdi mdi-view-list"></i>
</span>