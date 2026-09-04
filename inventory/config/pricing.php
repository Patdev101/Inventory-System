<?php

return [
    // Same VAT rate the POS charges (see possystem's POS_TAX_RATE). Shown
    // here purely for transparency: selling_price is VAT-inclusive (the
    // shelf price customers pay), so this lets whoever sets the price see
    // the VAT-exclusive amount and VAT component that make it up —
    // Subtotal + VAT = Selling Price — before it ever reaches the POS.
    'vat_rate' => (float) env('VAT_RATE', 12),
];
