<?php

namespace App\Orchid\Resources;

use Orchid\Crud\Resource;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\TD;
use Orchid\Screen\Sight;

use App\Models\Account;
use App\Models\Currency;
use App\Models\TypeAccount;

class Accounts extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Account::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Input::make('name')
                ->title('Name')
                ->placeholder('Enter title here'),
            TextArea::make('description')
                ->type('text')
                ->max(255)
                ->title(__('Description'))
                ->placeholder(__('Description')),

            Select::make('type_id')
                ->fromModel(TypeAccount::class, 'name')
                ->empty()
                ->required()
                ->title(__('Type')),

            Select::make('badge_id')
                ->fromModel(Currency::class, 'code')
                ->empty()
                ->required()
                ->title(__('Currency')),

            Input::make('init_amount')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->required()
                ->title(__('Current Amount')),

            Input::make('limit')
                ->type('number')
                ->min(0)
                ->value(0)
                ->step(0.01)
                ->title(__('Credit card limit')),
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
            TD::make('description'),

            TD::make('badge_id', 'Currency')
                ->render(function ($model) {
                    return $model->currency->code;
                }),
            TD::make('type_id', 'Type')
                ->render(function ($model) {
                    return $model->type->name;
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
            Sight::make('badge_id', 'Currency')
                ->render(function ($model) {
                    return $model->currency->code;
                }),
            Sight::make('type_id', 'Type')
                ->render(function ($model) {
                    return $model->type->name;
                }),
            Sight::make('user_id', 'User')
                ->render(function ($model) {
                    return $model->user->name;
                }),
            Sight::make('updated_at', 'Update date')
                ->render(function ($model) {
                    return $model->updated_at->toDateTimeString();
                })
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
