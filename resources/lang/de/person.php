<?php
return [
    'unavailable_audits' => 'No Person Audits available',

    'updated' => [
        'metadata' => 'On :audit_created_at, :user_name [:audit_ip_address] updated this record via :audit_url',
        'modified' => [
            'id' => 'ID: <strong>:old</strong> <i class="mdi mdi-arrow-right"></i> <strong>:new</strong>',
            'name' => 'Name: <strong>:old</strong> <i class="mdi mdi-arrow-right"></i> <strong>:new</strong>',
            'first_name' => 'Vorname: <strong>:old</strong> <i class="mdi mdi-arrow-right"></i> <strong>:new</strong>',
            'birthday' => 'Geburtstag: <strong>:old</strong> <i class="mdi mdi-arrow-right"></i> <strong>:new</strong>',
        ],
    ],
    'created' => [
        'metadata' => 'On :audit_created_at, :user_name [:audit_ip_address] created this record via :audit_url',
        'modified' => [
            'id' => 'ID: <strong>:new</strong>',
            'name' => 'Name: <strong>:new</strong>',
            'first_name' => 'Vorname: <strong>:new</strong>',
            'birthday' => 'Geburtstag: <strong>:new</strong>',
        ],
    ],
];