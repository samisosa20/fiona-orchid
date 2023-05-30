<div class="mb-3">
    <div class="p-4 bg-white rounded shadow-sm h-100 d-flex flex-column">
        <small class="text-muted d-block mb-1">{{__('Category by group')}}</small>
        @foreach ($group_expensive as $group)
        @php
        $amount = is_array($group) ? $group['amount'] : $group->amount;
        @endphp
        <div class="py-4 px-3 border-bottom">
            <p class="text-black h5 fw-normal mb-0">
                {{ is_array($group) ? $group['name'] : $group->name }}
            </p>
            <p class="float-end mb-0 fw-semibold {{ $amount > 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($amount, 2, ',', '.') }} {{ is_array($group) ? "({$group['porcent']}%)" : (isset($group->porcent) ? "({$group->porcent}%)" : '') }}
            </p>
        </div>
        @endforeach
    </div>
</div>