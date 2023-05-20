<?php

namespace App\Orchid\Layouts\Reports;

use App\Orchid\Filters\ReportFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class ReportFiltersLayout extends Selection
{
    /**
     * @return string[]|Filter[]
     */
    public function filters(): array
    {
        return [
            ReportFilter::class,
        ];
    }
}
