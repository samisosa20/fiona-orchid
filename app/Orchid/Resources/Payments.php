<?php

namespace App\Orchid\Resources;

use App\Models\Account;
use App\Models\Category;
use App\Models\PlannedPayment;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Crud\Resource;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;

class Payments extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = PlannedPayment::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Select::make('account_id')
                ->fromModel(Account::class, 'name')
                ->empty()
                ->required()
                ->title(__('Account')),

            Select::make('category_id')
                ->fromModel(Category::where([
                    ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
                ])
                    ->selectRaw('categories.id, if(categories.category_id is null, categories.name, concat(categories.name, " (", b.name, ")")) as title, categories.category_id as category_father')
                    ->leftJoin('categories as b', 'b.id', 'categories.category_id')
                    ->orderBy('categories.name'), 'title')
                ->empty()
                ->required()
                ->title(__('Category')),

            Input::make('amount')
                ->type('number')
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Amount')),

            TextArea::make('description')
                ->type('text')
                ->max(255)
                ->title(__('Description'))
                ->placeholder(__('Description')),

            DateTimer::make('start_date')
                ->allowInput()
                ->format('Y-m-d')
                ->required()
                ->title(__('Start Date')),

            DateTimer::make('end_date')
                ->allowInput()
                ->format('Y-m-d')
                ->title(__('End Date')),

            Input::make('specific_day')
                ->type('number')
                ->value(1)
                ->min(1)
                ->max(31)
                ->required()
                ->title(__('Choose specific day')),
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
            TD::make('account_id', 'Account')
                ->render(function ($model) {
                    return $model->account->name;
                }),
            TD::make('category_id', 'Category')
                ->render(function ($model) {
                    return $model->category->name;
                }),

            TD::make('amount'),
            TD::make('start_date'),
            TD::make('specific_day'),
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
            Sight::make('account_id', 'Account')
                ->render(function ($model) {
                    return $model->account->name;
                }),
            Sight::make('category_id', 'Category')
                ->render(function ($model) {
                    return $model->category->name;
                }),

            Sight::make('amount'),
            Sight::make('description'),
            Sight::make('start_date'),
            Sight::make('end_date'),
            Sight::make('specific_day'),
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
