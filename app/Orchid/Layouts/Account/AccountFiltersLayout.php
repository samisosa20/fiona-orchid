<?php

namespace App\Orchid\Layouts\Account;

use App\Orchid\Filters\AccountFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class AccountFiltersLayout extends Selection
{
    /**
     * @return string[]|Filter[]
     */
    public function filters(): array
    {
        return [
            AccountFilter::class,
        ];
    }
}
