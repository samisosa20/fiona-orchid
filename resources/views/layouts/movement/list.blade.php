<div class="p-4">
    <div style="height: 350px; overflow-y: auto;">
        @isset($movements)
            @foreach ($movements as $movement)
                <div class="border-bottom border-2 border-secondary py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="">{{$movement->category->name}}</div>
                        <div class="{{$movement->amount > 0 ? 'text-success' : 'text-danger'}}">$ {{number_format($movement->amount, 2, ',', '.')}}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pb-1">
                        <div class="">{{$movement->date_purchase}}</div>
                        <div class="">{{$movement->account->name}}</div>
                    </div>
                    @if($movement->description !== null)
                        <div class="border-top pt-1">{{$movement->description}}</div>
                    @endif
                </div>
            @endforeach
        @endisset
    </div>
</div>
