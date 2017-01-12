<span>
    <i class="mdi mdi-home"></i>
    {{ $entity->HAUS_STRASSE }} {{ $entity->HAUS_NUMMER }}
    <a href="{{ route('web::einheiten::legacy', ['einheit_raus' => 'einheit_kurz', 'haus_id' => $entity->HAUS_ID]) }}"><i
                class="mdi mdi-view-list"></i></a>
</span>