<?php

declare(strict_types=1);

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class AccountFilter extends Filter
{
    /**
     * @return string
     */
    public function name(): string
    {
        return __('Acount');
    }

    /**
     * The array of matched parameters.
     *
     * @return array|null
     */
    public function parameters(): ?array
    {
        return ['status'];
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
            Select::make('status')
            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
            ->value($this->request->get('status'))
            ->title(__('Status')),
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
