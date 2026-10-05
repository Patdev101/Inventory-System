<?php

return [
    /*
     * When on (the default), staff and managers only see and change data for
     * the location they are assigned to; a staff or manager account with no
     * location sees nothing until an admin assigns one. Admins are never
     * restricted. Turn off for a single-location business.
     */
    'restrict_users_to_location' => (bool) env('INVENTORY_RESTRICT_USERS_TO_LOCATION', true),
];
