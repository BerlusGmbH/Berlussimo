@php($name = $entity->DETAIL_NAME)
@if($name == "Telefon" || $name == "Handy")
    @include('shared.entities.details.telefon')
@elseif($name == "Hinweis")
    @include('shared.entities.details.hinweis')
@elseif($name == "Email")
    @include('shared.entities.details.email')
@elseif($name == 'Zustellanschrift' || $name == 'Verzugsanschrift' || $name == 'Anschrift')
    @include('shared.entities.details.adresse')
@else
    @include('shared.entities.details.detail')
@endif