<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Category;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Orchid\Layouts\Category\CategoryListLayout;

use App\Models\Category;
use App\Models\Movement;

class CategoryListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $categories = Category::withTrashed()
        ->where([
            ['user_id', $request->user()->id],
            ['group_id', '<>', env('GROUP_TRANSFER_ID')]
        ])
        ->with(['group', 'categoryFather'])
        ->addSelect([
            'sub_categories' => \DB::table('categories as b')
            ->selectRaw('count(*)')
            ->whereNull('b.deleted_at')
            ->whereColumn('categories.id', 'b.category_id')
        ])
        ->when($request->query('category_father'), function ($query) use ($request) {
            $query->where('category_id', $request->query('category_father'));
        })
        ->when(!$request->query('category_father'), function ($query) use ($request) {
            $query->whereNull('category_id');
        })
        ->paginate();


        return [
            'categories' => $categories,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Categories';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Register all your Categories';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('plus')
                ->route('platform.categories.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            CategoryListLayout::class,
        ];
    }



    /**
     * @param Request $request
     */
    public function activate(Request $request): void
    {
        Event::onlyTrashed()->find($request->get('id'))->restore();

        Toast::success(__('The Event was activated.'));
    }
}
