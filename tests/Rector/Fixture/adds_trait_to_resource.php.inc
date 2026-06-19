<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

class OrderResource extends Resource
{
}

?>
-----
<?php

namespace App\Filament\Resources;

use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;
use Filament\Resources\Resource;

class OrderResource extends Resource
{
    use TranslatesFilamentModelLabels;
}

?>
