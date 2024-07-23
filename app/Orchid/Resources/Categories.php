<?php

namespace App\Orchid\Resources;

use App\Models\Category;
use App\Models\Group;

use Orchid\Crud\Resource;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Sight;

class Categories extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = Category::class;

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

            TextArea::make('description')
                ->type('text')
                ->max(100)
                ->title(__('Description'))
                ->placeholder(__('Description')),

            Select::make('group_id')
                ->fromModel(Group::where([['id', '<>', env('GROUP_TRANSFER_ID')]]), 'name')
                ->empty()
                ->required()
                ->title(__('Group')),

            Select::make('category_id')
                ->fromModel(Category::where([
                    ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
                ])
                    ->selectRaw('categories.id, if(categories.category_id is null, categories.name, concat(categories.name, " (", b.name, ")")) as title, categories.category_id as category_father')
                    ->leftJoin('categories as b', 'b.id', 'categories.category_id')
                    ->orderBy('categories.name'), 'title')
                ->empty()
                ->title(__('Category Father'))
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
            TD::make('group_id', 'Group')
                ->render(function ($model) {
                    return $model->group->name;
                }),
            TD::make('category_id', 'Category Father')
                ->render(function ($model) {
                    return $model->categoryFather->name ?? '';
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
            Sight::make('description'),
            Sight::make('group_id', 'Group')
                ->render(function ($model) {
                    return $model->group->name;
                }),
            Sight::make('category_id', 'Category Father')
                ->render(function ($model) {
                    return $model->categoryFather->name ?? '';
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
