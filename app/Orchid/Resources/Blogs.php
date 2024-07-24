<?php

namespace App\Orchid\Resources;

use App\Models\Blog;

use Orchid\Crud\Resource;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\TD;

class Blogs extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Blog::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Switcher::make('published')
                ->title('Is public?')
                ->sendTrueOrFalse()
                ->placeholder('Do you want public this blog?'),
            Input::make('title')
                ->title('title')
                ->required()
                ->maxlength(250)
                ->placeholder('Enter title here'),
            TextArea::make('description')
                ->title('description')
                ->maxlength(250)
                ->required()
                ->placeholder('Enter description here'),
            Input::make('slug')
                ->title('slug')
                ->required()
                ->placeholder('Enter slug here'),
            Quill::make('content')
                ->title('content')
                ->required()
                ->placeholder('Enter slug here')
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
            TD::make('title'),
            TD::make('slug'),
            TD::make('published')
            ->render(function ($model) {
                return $model->published ? 'On' : 'Off';
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
