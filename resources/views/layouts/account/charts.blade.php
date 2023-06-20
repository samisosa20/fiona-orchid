<div class="bg-white rounded shadow-sm mb-3 pt-3">
    <div class="d-flex px-3 align-items-center">
        <legend class="text-black px-2 mt-2 mb-0">
            <div class="d-flex align-items-center">
                <small class="d-block">Balance</small>
            </div>
        </legend>
    </div>
    <div class="position-relative w-100">
        <canvas id="balances" data-balance="{{json_encode($balancesAccount)}}"></canvas>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/app.js'])
@endpush
