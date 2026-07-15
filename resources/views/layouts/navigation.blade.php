@php
    $role = auth()->user()->role ?? null;

    $dashboardRoute = match($role) {
        'admin'    => route('dashboard.admin'),
        'penderma' => route('dashboard.penderma'),
        'pelajar'  => route('dashboard.pelajar'),
        default    => '#',
    };

    $dashboardActive = match($role) {
        'admin' => request()->routeIs('dashboard.admin'),
        'penderma' => request()->routeIs('dashboard.penderma'),
        'pelajar' => request()->routeIs('dashboard.pelajar'),
        default => false,
    };
@endphp

@if($role === 'admin')
    @include('layouts.navigation.admin')
@else

<nav x-data="{ open: false }"
     class="bg-gradient-to-r from-slate-900 to-slate-950 border-b border-slate-800">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- LEFT -->
            <div class="flex items-center">

                <!-- LOGO -->
                    <div class="brand-mark">
    <svg viewBox="0 0 64 64" fill="none">

        <!-- CENTER -->
        <circle cx="32" cy="32" r="6"
                fill="currentColor"/>

        <!-- TOP -->
        <path d="M32 10L42 16V28L32 34L22 28V16L32 10Z"
              stroke="currentColor"
              stroke-width="3"
              stroke-linejoin="round"/>

        <!-- LEFT -->
        <path d="M14 36L24 42V54L14 60L4 54V42L14 36Z"
              stroke="currentColor"
              stroke-width="3"
              stroke-linejoin="round"/>

        <!-- RIGHT -->
        <path d="M50 36L60 42V54L50 60L40 54V42L50 36Z"
              stroke="currentColor"
              stroke-width="3"
              stroke-linejoin="round"/>

        <!-- CONNECTIONS -->
        <path d="M32 32L32 34"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"/>

        <path d="M27 36L22 42"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"/>

        <path d="M37 36L42 42"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"/>
    </svg>
</div>

                <!-- DESKTOP NAV -->
                <div class="hidden space-x-8 sm:ms-10 sm:flex">

                    {{-- ================= PELAJAR ================= --}}
                    @if($role === 'pelajar')

                        <x-nav-link :href="route('dashboard.pelajar')"
                                    :active="request()->routeIs('dashboard.pelajar')">
                            Dashboard
                        </x-nav-link>

                        <x-nav-link :href="route('permohonan.index')"
                                    :active="request()->routeIs('permohonan.index')">
                            Permohonan
                        </x-nav-link>

                        <x-nav-link :href="route('status.index')"
                                    :active="request()->routeIs('status.index')">
                            Status Permohonan
                        </x-nav-link>

                        <!-- Dropdown -->
                        <div class="relative group h-full flex items-center">
                            <button class="inline-flex items-center px-1 pt-1 text-sm font-medium
                                text-slate-300 hover:text-white border-b-2 border-transparent hover:border-white transition h-full">
                                Jenis Bantuan
                                <span class="ml-1 text-xs">▼</span>
                            </button>

                            <div class="absolute left-0 top-full pt-3 hidden group-hover:block z-50">
                                <div class="bg-white rounded-xl shadow-xl w-52 overflow-hidden border">

                                    <a href="{{ route('bantuan.asas') }}"
                                       class="block px-5 py-3 text-sm hover:bg-gray-50">
                                        Keperluan Asas
                                    </a>

                                    <a href="{{ route('pembelajaran') }}"
                                       class="block px-5 py-3 text-sm hover:bg-gray-50">
                                        Pembelajaran
                                    </a>

                                    <a href="{{ route('sukan') }}"
                                       class="block px-5 py-3 text-sm hover:bg-gray-50">
                                        Sukan
                                    </a>

                                </div>
                            </div>
                        </div>

                    @endif


                    {{-- ================= ADMIN ================= --}}
                    @if($role === 'admin')
                        @php
                            $adminDashboardHref = route('dashboard.admin');

                            $adminPendermaHref = Route::has('admin.penderma.landing')
                                ? route('admin.penderma.landing')
                                : (Route::has('admin.penderma.index') ? route('admin.penderma.index') : '#');

                            $adminDashboardActive = request()->routeIs('dashboard.admin');
                            $adminPelajarActive = request()->routeIs('admin.permohonan.*') || request()->routeIs('admin.agihan.*');
                            $adminPendermaActive = request()->routeIs('admin.penderma.*') || request()->routeIs('admin.sumbangan.*') || request()->routeIs('admin.tabung.*');
                            $adminAnalisisActive = request()->routeIs('admin.laporan.*') || request()->routeIs('admin.statistik.*');

                            $adminDropdowns = [
                                [
                                    'label' => 'Pelajar',
                                    'active' => $adminPelajarActive,
                                    'items' => [
                                        [
                                            'title' => 'Semak Permohonan',
                                            'href' => Route::has('admin.permohonan.index') ? route('admin.permohonan.index') : '#',
                                            'active' => request()->routeIs('admin.permohonan.index'),
                                        ],
                                        [
                                            'title' => 'Agihan Bantuan',
                                            'href' => Route::has('admin.agihan.index') ? route('admin.agihan.index') : '#',
                                            'active' => request()->routeIs('admin.agihan.*'),
                                        ],
                                    ],
                                ],
                                [
                                    'label' => 'Penderma',
                                    'active' => $adminPendermaActive,
                                    'items' => [
                                        [
                                            'title' => 'Senarai Penderma',
                                            'href' => route('admin.penderma.index'),
                                            'active' => request()->routeIs('admin.penderma.index'),
                                        ],
                                        [
                                            'title' => 'Daftar Penderma',
                                            'href' => Route::has('admin.penderma.create') ? route('admin.penderma.create') : '#',
                                            'active' => request()->routeIs('admin.penderma.create'),
                                        ],
                                        [
                                            'title' => 'Sumbangan',
                                            'href' => Route::has('admin.sumbangan.index') ? route('admin.sumbangan.index') : '#',
                                            'active' => request()->routeIs('admin.sumbangan.*'),
                                        ],
                                    ],
                                ],
                                [
                                    'label' => 'Analisis',
                                    'active' => $adminAnalisisActive,
                                    'items' => [
                                        [
                                            'title' => 'Laporan',
                                            'href' => Route::has('admin.laporan.index') ? route('admin.laporan.index') : '#',
                                            'active' => request()->routeIs('admin.laporan.*'),
                                        ],
                                        [
                                            'title' => 'Statistik Permohonan',
                                            'href' => Route::has('admin.statistik.permohonan') ? route('admin.statistik.permohonan') : '#',
                                            'active' => request()->routeIs('admin.statistik.permohonan'),
                                        ],
                                        [
                                            'title' => 'Statistik Sumbangan',
                                            'href' => Route::has('admin.statistik.sumbangan') ? route('admin.statistik.sumbangan') : '#',
                                            'active' => request()->routeIs('admin.statistik.sumbangan'),
                                        ],
                                        [
                                            'title' => 'Statistik Inventori',
                                            'href' => Route::has('admin.statistik.inventori') ? route('admin.statistik.inventori') : '#',
                                            'active' => request()->routeIs('admin.statistik.inventori'),
                                        ],
                                    ],
                                ],
                            ];
                        @endphp

                        <x-nav-link :href="$adminDashboardHref"
                                    :active="$adminDashboardActive">
                            Dashboard
                        </x-nav-link>

                        @foreach($adminDropdowns as $dropdown)
                            <div
                                x-data="{ dropdownOpen: false }"
                                @mouseenter="dropdownOpen = true"
                                @mouseleave="dropdownOpen = false"
                                class="relative flex h-full items-center"
                            >
                                <button
                                    type="button"
                                    @click="dropdownOpen = ! dropdownOpen"
                                    class="inline-flex h-full items-center border-b-2 px-1 pt-1 text-sm transition {{ $dropdown['active'] ? 'border-blue-400 font-semibold text-white' : 'border-transparent font-medium text-slate-300 hover:border-slate-500 hover:text-white' }}"
                                >
                                    {{ $dropdown['label'] }}
                                    <svg class="ms-1 h-4 w-4 fill-current transition"
                                         :class="{ 'rotate-180': dropdownOpen }"
                                         viewBox="0 0 20 20">
                                        <path d="M5.293 7.293L10 12l4.707-4.707" />
                                    </svg>
                                </button>

                                <div
                                    x-cloak
                                    x-show="dropdownOpen"
                                    x-transition.origin.top.left
                                    class="absolute left-0 top-full z-50 pt-3"
                                >
                                    <div class="w-60 overflow-hidden rounded-xl border border-slate-100 bg-white py-2 shadow-lg">
                                        @foreach($dropdown['items'] as $item)
                                            <a
                                                href="{{ $item['href'] }}"
                                                class="block px-4 py-3 text-sm font-semibold text-slate-900 transition {{ $item['active'] ? 'bg-slate-100' : 'hover:bg-slate-50' }}"
                                            >
                                                {{ $item['title'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    @endif


                    {{-- ================= PENDERMA ================= --}}
                    @if($role === 'penderma')

                        <x-nav-link :href="route('dashboard.penderma')"
                                    :active="request()->routeIs('dashboard.penderma')">
                            Dashboard
                        </x-nav-link>

                        <x-nav-link :href="route('penderma.sumbangan')"
                                    :active="request()->routeIs('penderma.sumbangan') || request()->routeIs('penderma.keperluan-sumbang') || request()->routeIs('penderma.pembelajaran-sumbang') || request()->routeIs('penderma.sukan-sumbang')">
                            Jenis Sumbangan
                        </x-nav-link>

                        <x-nav-link :href="route('penderma.menyumbang-bantuan')"
                                    :active="request()->routeIs('penderma.menyumbang-bantuan') || request()->routeIs('penderma.checkout-sumbangan') || request()->routeIs('penderma.toyyibpay.return')">
                            Borang Bantuan
                        </x-nav-link>

                        <x-nav-link :href="route('penderma.sejarah-sumbangan')"
                                    :active="request()->routeIs('penderma.sejarah-sumbangan') || request()->routeIs('penderma.sejarah-sumbangan.show')">
                            Sejarah Sumbangan
                        </x-nav-link>

                    @endif

                </div>
            </div>

            <!-- RIGHT -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-4">

                {{-- CART (PENDERMA SAHAJA) --}}
                @if($role === 'penderma')
                <div class="relative">
                    <button id="cartBtn"
                        class="relative flex items-center justify-center hover:scale-110 transition">
                        <img src="{{ asset('image/ui/trolley.jpg') }}"
                             alt="Cart"
                             class="w-8 h-8 object-contain">

                        <span id="cartCount"
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full">
                            0
                        </span>

                    </button>
                </div>
                @endif

                {{-- USER --}}
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md
                                text-slate-200 border border-slate-600 hover:bg-slate-700 transition">

                                {{ Auth::user()->name }}

                                <svg class="ms-2 h-4 w-4 fill-current"
                                     viewBox="0 0 20 20">
                                    <path d="M5.293 7.293L10 12l4.707-4.707" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">

                        <x-dropdown-link :href="route('profile.edit')">
                            Maklumat Profil
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('profile.password')">
                            Tukar Kata Laluan
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth

            </div>

            <!-- MOBILE BUTTON -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open"
                        class="p-2 text-slate-300 hover:text-white">
                    ☰
                </button>
            </div>

        </div>
    </div>

    <!-- MOBILE MENU -->
    <div x-show="open" class="sm:hidden bg-slate-900">

        <x-responsive-nav-link :href="$dashboardRoute" :active="$dashboardActive">
            Dashboard
        </x-responsive-nav-link>

        @if($role === 'admin')
            <x-responsive-nav-link :href="Route::has('admin.permohonan.index') ? route('admin.permohonan.index') : '#'"
                                   :active="request()->routeIs('admin.permohonan.index')">
                Semak Permohonan
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="Route::has('admin.agihan.index') ? route('admin.agihan.index') : '#'"
                                   :active="request()->routeIs('admin.agihan.*')">
                Agihan Bantuan
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="Route::has('admin.penderma.landing') ? route('admin.penderma.landing') : (Route::has('admin.penderma.index') ? route('admin.penderma.index') : '#')"
                                   :active="request()->routeIs('admin.penderma.*')">
                Penderma
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="Route::has('admin.sumbangan.index') ? route('admin.sumbangan.index') : '#'"
                                   :active="request()->routeIs('admin.sumbangan.*')">
                Sumbangan
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="Route::has('admin.laporan.index') ? route('admin.laporan.index') : '#'"
                                   :active="request()->routeIs('admin.laporan.*')">
                Laporan
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="Route::has('admin.statistik.permohonan') ? route('admin.statistik.permohonan') : '#'"
                                   :active="request()->routeIs('admin.statistik.permohonan')">
                Statistik Permohonan
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="Route::has('admin.statistik.sumbangan') ? route('admin.statistik.sumbangan') : '#'"
                                   :active="request()->routeIs('admin.statistik.sumbangan')">
                Statistik Sumbangan
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="Route::has('admin.statistik.inventori') ? route('admin.statistik.inventori') : '#'"
                                   :active="request()->routeIs('admin.statistik.inventori')">
                Statistik Inventori
            </x-responsive-nav-link>
        @endif

        @if($role === 'penderma')
            <x-responsive-nav-link :href="route('penderma.sumbangan')"
                                   :active="request()->routeIs('penderma.sumbangan') || request()->routeIs('penderma.keperluan-sumbang') || request()->routeIs('penderma.pembelajaran-sumbang') || request()->routeIs('penderma.sukan-sumbang')">
                Jenis Sumbangan
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('penderma.menyumbang-bantuan')"
                                   :active="request()->routeIs('penderma.menyumbang-bantuan') || request()->routeIs('penderma.checkout-sumbangan') || request()->routeIs('penderma.toyyibpay.return')">
                Borang Bantuan
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('penderma.sejarah-sumbangan')"
                                   :active="request()->routeIs('penderma.sejarah-sumbangan') || request()->routeIs('penderma.sejarah-sumbangan.show')">
                Sejarah Sumbangan
            </x-responsive-nav-link>
        @endif

        @auth
            <div class="mt-3 border-t border-slate-700 pt-3">
                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                    Maklumat Profil
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('profile.password')" :active="request()->routeIs('profile.password')">
                    Tukar Kata Laluan
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        :active="false">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        @endauth

    </div>

</nav>
@endif
