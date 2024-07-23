<?php

namespace App\Orchid\Resources;

use App\Models\Currency;
use App\Models\Investment;

use Orchid\Crud\Resource;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Sight;

class Investments extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Investment::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Input::make('name')
                ->type('text')
                ->max(100)
                ->required()
                ->title(__('Name'))
                ->placeholder(__('Name')),

            Input::make('init_amount')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Start Amount')),

            Select::make('badge_id')
                ->fromModel(Currency::class, 'code')
                ->empty()
                ->required()
                ->title(__('Currency')),

            DateTimer::make('date_investment')
                ->allowInput()
                ->format('Y-m-d')
                ->required()
                ->title(__('Date Investment')),
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
            TD::make('name'),
            TD::make('init_amount'),
            TD::make('date_investment'),
            TD::make('badge_id', 'Currency')
                ->render(function ($model) {
                    return $model->currency->code;
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
            Sight::make('name'),
            Sight::make('init_amount'),
            Sight::make('date_investment'),
            Sight::make('badge_id', 'Currency')
                ->render(function ($model) {
                    return $model->currency->code;
                }),
            Sight::make('user_id', 'User')
                ->render(function ($model) {
                    return $model->user->name;
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
