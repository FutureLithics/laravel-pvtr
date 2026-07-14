<?php

namespace App\Enums;

enum ImportStatus: string
{
    case Completed = 'completed';
    case Failed = 'failed';
}
