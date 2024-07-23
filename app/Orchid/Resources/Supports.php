<?php

namespace App\Orchid\Resources;

use App\Models\Support;
use Orchid\Crud\Resource;
use Orchid\Screen\Sight;
use Orchid\Screen\TD;

class Supports extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Support::class;

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
            TD::make('subject'),
            TD::make('message'),

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
            Sight::make('subject'),
            Sight::make('message'),

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
