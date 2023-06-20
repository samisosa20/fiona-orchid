<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Category;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Fields\Input;

use App\Models\Category;

class CategoryListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'categories';

    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('You currently have no Categories created');
    }

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Category $category) => DropDown::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.categories.edit', $category->id)
                            ->icon('pencil'),
                    ])),

            TD::make('name', __('Name'))
                ->sort()
                ->cantHide()
                ->render(fn (Category $category) => $category->name),
            
            TD::make('group_id', __('Group'))
                ->sort()
                ->cantHide()
                ->render(fn (Category $category) => $category->group->name),
            
            TD::make('sub_category', __('Sub Category'))
                ->render(fn (Category $category) => $category->sub_categories),

        ];
    }
}
