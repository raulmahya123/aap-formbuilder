@props(['options', 'filters', 'showDepartment' => true])
@php
    $allowedCompanyIds = $options['sites']->pluck('company_id')->filter()->unique()->map(fn ($id) => (int) $id)->all();
@endphp
<div class="ui-filter-field">
    <label class="ui-filter-field__label" for="module-site-filter">Site</label>
    <select id="module-site-filter" name="site_id" class="ui-table-filter" onchange="if(this.value){ this.form.elements.company_id.value = ''; }">
        <option value="">Semua site yang diizinkan</option>
        @foreach($options['sites'] as $site)
            <option value="{{ $site->id }}" @selected((string) request('site_id', $filters['site_id'] ?? '') === (string) $site->id)>
                {{ $site->code ? $site->code.' — ' : '' }}{{ $site->name }}
            </option>
        @endforeach
    </select>
</div>
<div class="ui-filter-field">
    <label class="ui-filter-field__label" for="module-company-filter">Perusahaan</label>
    <select id="module-company-filter" name="company_id" class="ui-table-filter" onchange="if(this.value){ this.form.elements.site_id.value = ''; }">
        <option value="">Semua perusahaan</option>
        @foreach($options['companies'] as $company)
            @if($filters['allowed_site_ids'] === null || in_array((int) $company->id, $allowedCompanyIds, true))
            <option value="{{ $company->id }}" @selected((string) request('company_id', $filters['company_id'] ?? '') === (string) $company->id)>
                {{ $company->name }}
            </option>
            @endif
        @endforeach
    </select>
</div>
@if($showDepartment)
    <div class="ui-filter-field">
        <label class="ui-filter-field__label" for="module-department-filter">Department</label>
        <select id="module-department-filter" name="department_id" class="ui-table-filter">
            <option value="">Semua department</option>
            @foreach($options['departments'] as $department)
                <option value="{{ $department->id }}" @selected((string) request('department_id', $filters['department_id'] ?? '') === (string) $department->id)>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>
@endif
