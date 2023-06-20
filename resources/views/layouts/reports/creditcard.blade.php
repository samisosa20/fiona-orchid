<div class="mb-3">
    <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
        <small class="text-muted d-block mb-1">{{__('Credit card Utilization')}}</small>
        @foreach ($credit_carts as $credit)
        @php
        $porcent = round(abs($credit->balance) / $credit->limit * 100, 2);
        @endphp
        <div class="py-4 px-3 border-bottom">
            <p>{{$credit->name}}</p>
            <div>
                <small>{{number_format(abs($credit->balance))}} ({{$porcent}}%)</small>
                <small class="float-end">{{number_format($credit->limit)}} {{$credit->currency->code}}</small>
            </div>
            <div class="progress">
                <div class="progress-bar bg-danger rounded" role="progressbar" aria-valuenow="{{$porcent}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$porcent}}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>