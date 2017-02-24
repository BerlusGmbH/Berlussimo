<span style="white-space: nowrap">
    @php
        if(!isset($icons))
        {
            $icons = true;
        };
    @endphp
    @if($icons)
        <i class="mdi mdi-account-multiple tooltipped" data-position="bottom" data-delay="50" data-tooltip="Partner"></i>
    @endif
    <a class="tooltipped" data-position="bottom" data-delay="50" data-tooltip="{{ trim($entity->PARTNER_NAME) }}" href="{{ route('web::partner::legacy', ['option' => 'partner_im_detail', 'partner_id' => $entity->PARTNER_ID]) }}">{{ str_limit(trim($entity->PARTNER_NAME), 20) }}</a>
</span>