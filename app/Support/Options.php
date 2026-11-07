<?php

namespace App\Support;

/**
 * Select options shared by the registration form, the workspace and validation.
 */
class Options
{
    public const TITLES = ['Brother', 'Sister', 'Deacon', 'Deaconess', 'Pastor', 'Reverend'];

    public const DESIGNATIONS = [
        'Non-Pastoring',
        'BLW Group Secretary',
        'BLW Zonal Secretary',
        'BLW Regional Secretary',
        'Church Pastor',
        'Sub-Group Pastor',
        'Group Pastor',
        'Asst. Zonal Pastor',
        'Zonal Pastor',
        'Zonal Director',
        'Regional Pastor',
    ];
}
