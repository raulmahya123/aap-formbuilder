@props(['filters', 'options', 'showDepartment' => true])
<form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-end gap-3" x-data="{ period: '{{ $filters['period'] }}' }">
    <div class="grid gap-1">
        <label class="text-xs font-medium text-[#52525B]" for="hse-period">Periode</label>
        <select id="hse-period" name="period" x-model="period" class="ui-input">
            <option value="monthly">Bulanan</option>
            <option value="quarterly">Triwulan</option>
            <option value="yearly">Tahunan</option>
        </select>
    </div>

    <div class="grid gap-1">
        <label class="text-xs font-medium text-[#52525B]" for="hse-year">Tahun</label>
        <select id="hse-year" name="year" class="ui-input">
            @foreach($options['years'] as $year)
                <option value="{{ $year }}" @selected((int) $filters['year'] === (int) $year)>{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid gap-1" x-show="period === 'monthly'" x-cloak>
        <label class="text-xs font-medium text-[#52525B]" for="hse-month">Bulan</label>
        <select id="hse-month" name="month" class="ui-input">
            @foreach(range(1, 12) as $month)
                <option value="{{ $month }}" @selected((int) $filters['month'] === $month)>{{ \Carbon\Carbon::createFromDate(2000, $month, 1)->translatedFormat('F') }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid gap-1" x-show="period === 'quarterly'" x-cloak>
        <label class="text-xs font-medium text-[#52525B]" for="hse-quarter">Triwulan</label>
        <select id="hse-quarter" name="quarter" class="ui-input">
            @foreach(range(1, 4) as $quarter)
                <option value="{{ $quarter }}" @selected((int) $filters['quarter'] === $quarter)>Q{{ $quarter }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid gap-1">
        <label class="text-xs font-medium text-[#52525B]" for="hse-site">Site</label>
        <select id="hse-site" name="site_id" class="ui-input">
            <option value="">Semua Site</option>
            @foreach($options['sites'] as $site)
                <option value="{{ $site->id }}" @selected((int) $filters['site_id'] === (int) $site->id)>
                    {{ $site->code ? $site->code.' — ' : '' }}{{ $site->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="grid gap-1">
        <label class="text-xs font-medium text-[#52525B]" for="hse-company">Perusahaan</label>
        <select id="hse-company" name="company_id" class="ui-input">
            <option value="">Semua Perusahaan</option>
            @foreach($options['companies'] as $company)
                <option value="{{ $company->id }}" @selected((int) $filters['company_id'] === (int) $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
    </div>

    @if($showDepartment)
        <div class="grid gap-1">
            <label class="text-xs font-medium text-[#52525B]" for="hse-department">Department</label>
            <select id="hse-department" name="department_id" class="ui-input">
                <option value="">Semua Department</option>
                @foreach($options['departments'] as $department)
                    <option value="{{ $department->id }}" @selected((int) $filters['department_id'] === (int) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="grid gap-1">
        <label class="text-xs font-medium text-[#52525B]" for="hse-date-from">Dari (custom)</label>
        <input id="hse-date-from" type="date" name="date_from" value="{{ request('date_from') }}" class="ui-input">
    </div>

    <div class="grid gap-1">
        <label class="text-xs font-medium text-[#52525B]" for="hse-date-to">Sampai (custom)</label>
        <input id="hse-date-to" type="date" name="date_to" value="{{ request('date_to') }}" class="ui-input">
    </div>

    <div class="flex items-center gap-2 pb-0.5">
        <button type="submit" class="ui-button ui-button--primary">Terapkan</button>
        <a href="{{ url()->current() }}" class="ui-button">Reset</a>
    </div>
</form>
