<div class="mb-3">
    <div class="row mb-2 g-3 g-mb-4">
        <div class="col">
            <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
                <small class="text-muted d-block mb-1">{{ __('Current Month') }}</small>
                @foreach ($balances as $balance)
                    <p class="h3 text-black fw-light mt-auto">
                        {{number_format($balance->month, 2, ',', '.')}} {{$balance->currency}}
                    </p>
                @endforeach
            </div>
        </div>
        <div class="col">
            <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
                <small class="text-muted d-block mb-1">{{ __('Current Year') }}</small>
                @foreach ($balances as $balance)
                    <p class="h3 text-black fw-light mt-auto">
                        {{number_format($balance->year, 2, ',', '.')}} {{$balance->currency}}
                    </p>
                @endforeach
            </div>
        </div>
        <div class="col">
            <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
                <small class="text-muted d-block mb-1">{{ __('Total Balance') }}</small>
                @foreach ($balances as $balance)
                    <p class="h3 text-black fw-light mt-auto">
                        {{number_format($balance->balance, 2, ',', '.')}} {{$balance->currency}}
                    </p>
                @endforeach
            </div>
        </div>
    </div>
</div>
