<?php

namespace App\Orchid\Resources;

use App\Models\Newsletter;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Crud\Resource;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\TD;

class NewsLetters extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Newsletter::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Input::make('subject')
                ->title('subject')
                ->required()
                ->placeholder('Enter subject here'),
            DateTimer::make('date_delivery')
                ->title('Delivery date')
                ->allowInput()
                ->format('Y-m-d')
                ->min(now())
                ->required(),
            Quill::make('content')
                ->title('content')
                ->required()
                ->placeholder('Enter the content here')
                ->base64(true),
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
            TD::make('subject')->width('200px'),
            TD::make('sended')
                ->render(function ($model) {
                    return $model->sended ? 'Yes' : 'No';
                }),
            TD::make('date_delivery', 'Date delivery')
                ->render(function ($model) {
                    return $model->created_at->toDateTimeString();
                }),
            TD::make('created_at', 'Date of creation')
                ->render(function ($model) {
                    return $model->created_at->toDateTimeString();
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
        return [];
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
}
