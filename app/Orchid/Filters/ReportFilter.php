<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;

use App\Models\Currency;
use App\Models\User;

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
        return ['start_date', 'end_date', 'badge_id'];
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {
        return $builder->where('badge_id', '=', 1);
    }

    /**
     * @return Field[]
     */
    public function display(): array
    {
        return [
            Select::make('badge_id')
            ->fromModel(Currency::class, 'code')
            ->value($this->request->get('badge_id') ?? $this->request->user()->badge_id)
            ->title(__('Currency')),
            DateTimer::make('start_date')
            ->value($this->request->get('start_date'))
            ->format('Y-m-d')
            ->title(__('Start Date')),
            DateTimer::make('end_date')
            ->value($this->request->get('end_date'))
            ->format('Y-m-d')
            ->title(__('End Date')),
        ];
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->name().': '. Currency::where('id', $this->request->get('badge_id'))->first()->code;
    }
}
