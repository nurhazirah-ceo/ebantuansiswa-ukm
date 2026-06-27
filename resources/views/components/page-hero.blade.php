@props([
    'eyebrow' => '',
    'title' => '',
    'description' => null,
])

<section {{ $attributes->merge([
    'class' => 'rounded-[1.5rem] bg-[#071633] px-6 py-8 text-white shadow-lg'
]) }}>

    <p class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-200">
        {{ $eyebrow ?? '' }}
    </p>

    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-white"
        style="font-family: 'Poppins', sans-serif;">
        {{ $title ?? '' }}
    </h1>

    @if($description ?? false)
        <p class="mt-2 max-w-3xl text-sm leading-5 text-slate-200"
           style="font-family: 'Poppins', sans-serif;">
            {{ $description }}
        </p>
    @endif

    {{ $slot }}

</section>
