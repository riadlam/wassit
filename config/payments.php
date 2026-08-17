<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Processor Fee
    |--------------------------------------------------------------------------
    |
    | Percentage added on top of the account price to cover payment handling.
    | Both the checkout summary and the amount charged by the payment gateway
    | read this value, so they can never drift apart.
    |
    */
    'processor_fee_percent' => (float) env('PROCESSOR_FEE_PERCENT', 1),
];
