<?php

namespace App\Orchid\Resources;

use App\Models\Movement;
use Orchid\Crud\Resource;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;

class Movements extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Movement::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [];
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
            TD::make('amount'),
            TD::make('date_purchase'),

            TD::make('category_id', 'Category')
                ->render(function ($model) {
                    return $model->category->name;
                }),
            TD::make('account_id', 'Account')
                ->render(function ($model) {
                    return $model->account->name;
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
            Sight::make('amount'),
            Sight::make('date_purchase'),
            Sight::make('trm'),
            Sight::make('description'),

            Sight::make('category_id', 'Category')
                ->render(function ($model) {
                    return $model->category->name;
                }),
            Sight::make('account_id', 'Account')
                ->render(function ($model) {
                    return $model->account->name;
                }),
            Sight::make('event_id', 'Event')
                ->render(function ($model) {
                    return $model->event->name ?? '';
                }),
            Sight::make('investment_id', 'Investment')
                ->render(function ($model) {
                    return $model->invesment->name ?? '';
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
