<div class="mb-3">
    <div class="row mb-2 g-3 g-mb-4">
        <div class="col">
            <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
                <small class="text-muted d-block mb-1">{{ __('Valuation') }}</small>
                @foreach ($balances as $balance)
                    <p class="h3 text-black fw-light mt-auto">
                        {{number_format($balance->valuation * 1, 2, ',', '.')}} {{$balance->currency}}
                    </p>
                @endforeach
            </div>
        </div>
        <div class="col">
            <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
                <small class="text-muted d-block mb-1">{{ __('Current Amount') }}</small>
                @foreach ($balances as $balance)
                    <p class="h3 text-black fw-light mt-auto">
                        {{number_format($balance->amount * 1, 2, ',', '.')}} {{$balance->currency}}
                    </p>
                @endforeach
            </div>
        </div>
        <div class="col">
            <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
                <small class="text-muted d-block mb-1">{{ __('Profits') }}</small>
                @foreach ($balances as $balance)
                    <p class="h3 text-black fw-light mt-auto">
                        {{number_format($balance->profit, 2, ',', '.')}} {{$balance->currency}}
                    </p>
                @endforeach
            </div>
        </div>
    </div>
</div>
