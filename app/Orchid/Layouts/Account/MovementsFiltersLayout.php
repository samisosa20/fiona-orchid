<?php

namespace App\Orchid\Layouts\Account;

use App\Orchid\Filters\MovementFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class MovementsFiltersLayout extends Selection
{
    /**
     * @return string[]|Filter[]
     */
    public function filters(): array
    {
        return [
            MovementFilter::class,
        ];
    }
}
