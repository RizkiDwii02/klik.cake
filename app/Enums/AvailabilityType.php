<?php

namespace App\Enums;

enum AvailabilityType: string
{
    case READY_TODAY = 'ready_today';
    case PREORDER = 'preorder';
}
