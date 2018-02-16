<span style="white-space: nowrap">
    @if($entity->isActive())
        <i class="mdi mdi-checkbox-blank tooltipped"
           data-tooltip="Kaufvertrag (Aktiv)"></i>
    @else
        <i class="mdi mdi-checkbox-blank-outline tooltipped"
           data-tooltip="Kaufvertrag (Inaktiv)"></i>
    @endif
    <a href="{{route('web::weg::legacy', ['option' => 'einheit_uebersicht', 'einheit_id' => $entity->EINHEIT_ID])}}">KV-{{$entity->ID}}</a>
    <a target="_blank" href="{{route('web::weg::legacy', ['option' => 'hg_kontoauszug', 'eigentuemer_id' => $entity->ID, 'jahr' => date('Y')])}}"><i class="mdi mdi-file-pdf tooltipped"
                                                                                                                                     data-tooltip="Hausgeldkontenblatt"></i></a>
</span>