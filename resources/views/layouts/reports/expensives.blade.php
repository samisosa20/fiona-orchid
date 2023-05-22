<div class="mb-3">
    <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
        <small class="text-muted d-block mb-1">{{__('List Expensives')}}</small>
        <div class="scroll-y" style="max-height: 358px;">
            @foreach ($list_expensives as $expensive)
            <div class="py-4 px-3 border-bottom">
                <p class="text-black h5 fw-normal mb-0">
                    {{ $expensive->category }}
                </p>
                <p class="float-end mb-0 fw-semibold {{ $expensive->amount > 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($expensive->amount, 2, ',', '.') }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</div>