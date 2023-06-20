<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\DateTimer;

use App\Models\Movement;
use App\Models\Event;

class MovementFilter extends Filter
{
    /**
     * @return string
     */
    public function name(): string
    {
        return __('Movements');
    }

    /**
     * The array of matched parameters.
     *
     * @return array|null
     */
    public function parameters(): ?array
    {
        return ['category', 'amount', 'description', 'event_id'];
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
            Input::make('category')
            ->value($this->request->get('category'))
            ->title(__('Category')),
            Input::make('amount')
            ->value($this->request->get('amount'))
            ->title(__('Amount')),
            Input::make('description')
            ->value($this->request->get('description'))
            ->title(__('Description')),
            Select::make('event_id')
            ->fromModel(Event::class, 'name')
            ->empty()
            ->value($this->request->get('event_id'))
            ->title(__('Event')),
        ];
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->name().': '.implode(',',array_values($this->request->query()));
    }
}
