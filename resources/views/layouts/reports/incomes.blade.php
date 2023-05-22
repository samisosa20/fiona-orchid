@push('stylesheets')
    @vite(['resources/css/app.css'])
@endpush
<div class="mb-3">
    <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
        <small class="text-muted d-block mb-1">{{__('List Incomes')}}</small>
        <div class="scroll-y" style="max-height: 358px;">
            @foreach ($list_incomes as $income)
            <div class="py-4 px-3 border-bottom">
                <p class="text-black h5 fw-normal mb-0">
                    {{ $income->category }}
                </p>
                <p class="float-end mb-0 fw-semibold {{ $income->amount > 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($income->amount, 2, ',', '.') }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</div>