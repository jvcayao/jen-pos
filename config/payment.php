<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Types Configuration
    |--------------------------------------------------------------------------
    |
    | Configure available payment methods for the POS system.
    | Each payment method has an id, name, and optional vendor information.
    |
    */

    'methods' => [
        'cash' => [
            'id' => 'cash',
            'name' => 'Cash',
            'vendor' => null,
            'enabled' => true,
        ],
        'gcash' => [
            'id' => 'gcash',
            'name' => 'G-Cash',
            'vendor' => 'gcash',
            'enabled' => true,
        ],
        'wallet' => [
            'id' => 'wallet',
            'name' => 'Student Wallet',
            'vendor' => 'wallet',
            'enabled' => true,
            'requires_student' => true,
        ],
        'card' => [
            'id' => 'card',
            'name' => 'Credit/Debit Card',
            'vendor' => null,
            'enabled' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Payment Method
    |--------------------------------------------------------------------------
    |
    | The default payment method when none is selected.
    |
    */

    'default' => 'cash',

    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    |
    | The default VAT/tax rate as a decimal (e.g., 0.12 = 12%).
    |
    */

    'tax_rate' => 0.12,
];
