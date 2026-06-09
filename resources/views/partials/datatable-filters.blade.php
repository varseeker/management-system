@props([
    'tableId',
    'filters' => [],
])

@if(count($filters))
<div class="row g-2 mb-3 align-items-end">
    <div class="col-md-4">
        <label class="form-label small mb-1">Cari</label>
        <input type="search"
            class="form-control form-control-sm js-dt-text-search"
            data-table="{{ $tableId }}"
            placeholder="Ketik kata kunci...">
    </div>

    @foreach($filters as $filter)
    <div class="col-md-{{ $filter['cols'] ?? 2 }}">
        <label class="form-label small mb-1">{{ $filter['label'] }}</label>
        <select
            class="form-select form-select-sm js-dt-column-filter"
            data-table="{{ $tableId }}"
            data-column="{{ $filter['column'] }}">
            <option value="">{{ $filter['placeholder'] ?? 'Semua' }}</option>
            @foreach($filter['options'] as $option)
            <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        </select>
    </div>
    @endforeach

    <div class="col-md-{{ count($filters) >= 3 ? 12 : 4 }}">
        <span class="small text-muted js-filter-info" data-table="{{ $tableId }}"></span>
    </div>
</div>
@endif

<p class="text-muted text-center py-3 mb-0 d-none js-filter-empty" data-table="{{ $tableId }}">
    Tidak ada data yang cocok dengan filter.
</p>
