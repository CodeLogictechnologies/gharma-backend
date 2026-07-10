<?php

return [
    // Selectable VAT rates an item can be taxed at.
    "taxable" => [
        13,
        5,
        15,
    ],

    // Rate pre-selected in the item form and used to backfill legacy items.
    "default" => 13,

    "non-taxable" => 0,
];
