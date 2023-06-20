<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Relation;

use App\Models\Group;
use App\Models\Category;

class CategoryEditLayout extends Rows
{
    /**
     * The screen's layout elements.
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return [
            Input::make('category.name')
                ->type('text')
                ->max(100)
                ->required()
                ->title(__('Name'))
                ->placeholder(__('Name')),
            
            TextArea::make('category.description')
                ->type('text')
                ->max(100)
                ->title(__('Description'))
                ->placeholder(__('Description')),

            Select::make('category.group_id')
                ->fromModel(Group::where([['id', '<>', env('GROUP_TRANSFER_ID')]]), 'name')
                ->empty()
                ->required()
                ->title(__('Group')),

            Select::make('category.category_id')
                ->fromModel(Category::where([
                    ['categories.user_id', $this->query['user']->id],
                    ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
                ])
                ->selectRaw('categories.id, if(categories.category_id is null, categories.name, concat(categories.name, " (", b.name, ")")) as title, categories.category_id as category_father')
                ->leftJoin('categories as b', 'b.id', 'categories.category_id')
                ->orderBy('categories.name'), 'title')
                ->empty()
                ->title(__('Category Father'))
        ];
    }
}
