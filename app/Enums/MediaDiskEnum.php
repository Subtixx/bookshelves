<?php

namespace App\Enums;

use App\Traits\LazyEnum;

enum MediaDiskEnum: string
{
    use LazyEnum;

    case cms = 'cms';
    case cover = 'cover';
    case format = 'format';
    case user = 'user';
}
