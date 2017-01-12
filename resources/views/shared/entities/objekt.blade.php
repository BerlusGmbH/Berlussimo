<span>
    <i class="mdi mdi-city"></i>
    {{ $entity->OBJEKT_KURZNAME }}
    <a href="{{ route('web::haeuser::legacy', ['haus_raus' => 'haus_kurz', 'objekt_id' => $entity->OBJEKT_ID]) }}"><i
                class="mdi mdi-view-list"></i></a>
</span>