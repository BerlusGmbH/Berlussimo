<b-messages-loader :success-messages="{{json_encode(session()->get(\App\Messages\SuccessMessage::TYPE))}}"
                   :info-messages="{{json_encode(session()->get(\App\Messages\InfoMessage::TYPE))}}"
                   :warning-messages="{{json_encode(session()->get(\App\Messages\WarningMessage::TYPE))}}"
                   :error-messages="{{json_encode(session()->get(\App\Messages\ErrorMessage::TYPE))}}">
</b-messages-loader>
<b-messages></b-messages>