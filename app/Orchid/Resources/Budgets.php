<?php

namespace App\Orchid\Resources;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Period;
use Orchid\Crud\Resource;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Sight;

class Budgets extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Budget::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Input::make('year')
                ->type('number')
                ->value(now()->format('Y'))
                ->required()
                ->title(__('Year')),

            Select::make('category_id')
                ->fromModel(Category::where([
                    ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
                ])
                    ->selectRaw('categories.id, if(categories.category_id is null, categories.name, concat(categories.name, " (", b.name, ")")) as title, categories.category_id as category_father')
                    ->leftJoin('categories as b', 'b.id', 'categories.category_id')
                    ->orderBy('categories.name'), 'title')
                ->empty()
                ->title(__('Category')),

            Select::make('badge_id')
                ->fromModel(Currency::class, 'code')
                ->empty()
                ->required()
                ->title(__('Currency')),

            Input::make('amount')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Amount')),

            Select::make('period_id')
                ->fromModel(Period::class, 'name')
                ->empty()
                ->required()
                ->title(__('Period')),
        ];
    }

    /**
     * Get the columns displayed by the resource.
     *
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id'),
            TD::make('year'),
            TD::make('category_id', 'Category')
                ->render(function ($model) {
                    return $model->category->name;
                }),
            TD::make('badge_id', 'Currency')
                ->render(function ($model) {
                    return $model->currency->code;
                }),
            TD::make('amount'),
            TD::make('period_id', 'Period')
                ->render(function ($model) {
                    return $model->period->name;
                }),
            TD::make('user_id', 'User')
                ->render(function ($model) {
                    return $model->user->name;
                }),
            TD::make('updated_at', 'Update date')
                ->render(function ($model) {
                    return $model->updated_at->toDateTimeString();
                }),
        ];
    }

    /**
     * Get the sights displayed by the resource.
     *
     * @return Sight[]
     */
    public function legend(): array
    {
        return [
            Sight::make('id'),
            Sight::make('year'),
            Sight::make('category_id', 'Category')
                ->render(function ($model) {
                    return $model->category->name;
                }),
            Sight::make('badge_id', 'Currency')
                ->render(function ($model) {
                    return $model->currency->code;
                }),
            Sight::make('amount'),
            Sight::make('period_id', 'Period')
                ->render(function ($model) {
                    return $model->period->name;
                }),
            Sight::make('user_id', 'User')
                ->render(function ($model) {
                    return $model->user->name;
                }),
            Sight::make('updated_at', 'Update date')
                ->render(function ($model) {
                    return $model->updated_at->toDateTimeString();
                }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(): array
    {
        return [];
    }


    /**
     * Get the permission key for the resource.
     *
     * @return string|null
     */
    public static function permission(): ?string
    {
        return 'platform.systems.users';
    }
}
