<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateRange;

use App\Models\Currency;

class ReportFilter extends Filter
{
    /**
     * @return string
     */
    public function name(): string
    {
        return __('Reports');
    }

    /**
     * The array of matched parameters.
     *
     * @return array|null
     */
    public function parameters(): ?array
    {
        return ['date', 'badge_id'];
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {
        return $builder->whereHas('roles', function (Builder $query) {
            $query->where('slug', $this->request->get('role'));
        });
    }

    /**
     * @return Field[]
     */
    public function display(): array
    {
        return [
            Select::make('badge_id')
            ->fromModel(Currency::class, 'code')
            ->empty()
            ->value($this->request->get('badge_id'))
            ->title(__('Currency')),
            DateRange::make('date')
            ->value($this->request->get('date'))
            ->title(__('Range Date')),
        ];
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->name().': '.Currency::where('id', $this->request->get('badge_id'))->first()->code;
    }
}
