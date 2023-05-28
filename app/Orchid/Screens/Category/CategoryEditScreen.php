<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Category;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Models\Category;

use App\Orchid\Layouts\Category\CategoryEditLayout;
use App\Orchid\Layouts\Category\CategoryListLayout;

class CategoryEditScreen extends Screen
{
    /**
     * @var Category
     */
    public $category;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param Category $category
     *
     * @return array
     */
    public function query(Category $category, Request $request): iterable
    {
        $categories = array();
        if($category->id) {
            $categories = Category::withTrashed()
            ->where([
                ['user_id', $request->user()->id],
                ['category_id', $category->id]
            ])
            ->with(['group', 'categoryFather'])
            ->addSelect([
                'sub_categories' => \DB::table('categories as b')
                ->selectRaw('count(*)')
                ->whereNull('b.deleted_at')
                ->whereColumn('categories.id', 'b.category_id')
            ])
            ->paginate();
        }
        
        return [
            'categories' => $categories,
            'category' => $category,
            'user' => $request->user(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->category->id ? 'Edit Category' : 'Create Category';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return '';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [

            Button::make(__('Remove'))
                ->icon('trash')
                ->confirm(__('Once the Category is deleted, all of its resources and data will be permanently deleted. Before deleting your Category, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->canSee(!!$this->category->id),

            Button::make(__('Save'))
                ->icon('check')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        $table = [];
        if($this->category->id){
            $table = CategoryListLayout::class;
        }
        return [
            Layout::block(CategoryEditLayout::class),
            $table,
        ];
    }

    /**
     * @param Category    $category
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Category $category, Request $request)
    {
        $category->fill($request->collect('category')->toArray())
            ->fill(['user_id' => $request->user()->id])
            ->save();

        Toast::info(__('Category was saved.'));

        return redirect()->route('platform.categories');
    }

    /**
     * @param Category $user
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Category $category)
    {
        $category->delete();

        Toast::info(__('Category was removed'));

        return redirect()->route('platform.categories');
    }

}
