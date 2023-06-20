<div class="mb-3">
    <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
        <small class="text-muted d-block mb-1">{{__('List Expensives')}}</small>
        <div class="scroll-y" style="max-height: 358px;">
            @foreach ($list_expensives as $expensive)
            <div class="py-4 px-3 border-bottom {{$expensive->category_id ? 'cursor' : ''}}" formaction="{{request()->server("HTTP_HOST").'/'.request()->path()}}/showMovementsModal" data-controller="modal-toggle"
                data-action="{{$expensive->category_id ? 'click->modal-toggle#targetModal' : null}}" data-modal-toggle-title="Lista de movimeintos"
                data-modal-toggle-key="movementsModal" data-modal-toggle-async="" data-modal-toggle-params="{&quot;category_id&quot;:{{$expensive->category_id}},&quot;start_date&quot;:&quot;{{$init_date}}&quot;,&quot;end_date&quot;:&quot;{{$end_date}}&quot;,&quot;badge_id&quot;:{{$currency}}}"
                data-modal-toggle-action="{{request()->server("HTTP_HOST").'/'.request()->path()}}/showMovementsModal" data-modal-toggle-open="">
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