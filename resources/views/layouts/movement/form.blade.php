<fieldset class="mb-3">
    <div class="bg-white rounded shadow-sm p-4 py-4 d-flex flex-column">
        {!! Orchid\Screen\Fields\Select::make('movement[account_id]')
            ->fromModel(App\Models\Account::class, 'name')
            ->empty()
            ->value($defaultAccount)
            ->required()
            ->title(__('Account'))
            !!}
        <div id="container-account_in" class="d-none mb-3">
            {!! Orchid\Screen\Fields\Select::make('movement[account_end_id]')
                ->fromModel(App\Models\Account::class, 'name')
                ->empty()
                ->required()
                ->value($defaultAccount)
                ->title(__('Account in'))
                !!}
        </div>
        <div id="container-movement" class="mb-3">
            {!! Orchid\Screen\Fields\Select::make('movement[category_id]')
                ->fromModel(App\Models\Category::where([
                    ['categories.user_id', $user->id],
                    ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
                    ])
                ->selectRaw('categories.id, if(categories.category_id is null, categories.name, concat(categories.name, " (", b.name, ")")) as title, categories.category_id as category_father')
                ->leftJoin('categories as b', 'b.id', 'categories.category_id')
                ->orderBy('categories.name'), 'title')
                ->required()
                ->empty()
                ->title(__('Category'))
            !!}
            {!! Orchid\Screen\Fields\Select::make('movement[event_id]')
                ->fromModel(App\Models\Event::where([
                    ['user_id', $user->id],
                ])
                ->whereDate('end_event', '>=', now()), 'name')
                ->empty()
                ->title(__('Event'))
            !!}
        </div>
        {!! Orchid\Screen\Fields\TextArea::make('movement[description]')
            ->title('Comentario')
            ->maxlength(250)
        !!}
    </div>
</fieldset>

@push('scripts')
    @vite(['resources/js/app.js'])
@endpush