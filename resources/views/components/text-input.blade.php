@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'h-10 rounded-lg border-[#C5C5C5] bg-white text-[#111111] shadow-none focus:border-[#0057D9] focus:ring-[#0057D9]']) }}>
