@php
    $user = auth()->user();

    $adminName = $user?->name ?? 'Pentadbir';
    $adminEmail = $user?->email ?? 'admin@sistem.local';
    $adminRole = ucfirst($user?->role ?? 'Admin');
    $adminPhoto = $user?->profile_photo_path
        ? asset('storage/' . $user->profile_photo_path)
        : asset('image/ui/ukm.jpg');

    $adminDashboardHref = route('dashboard.admin');

    $adminDashboardActive = request()->routeIs('dashboard.admin');
    $adminPelajarActive = request()->routeIs('admin.permohonan.*') || request()->routeIs('admin.agihan.*');
    $adminPendermaActive = request()->routeIs('admin.penderma.*');
    $adminAnalisisActive = request()->routeIs('admin.sumbangan.*') || request()->routeIs('admin.tabung.*') || request()->routeIs('admin.laporan.*') || request()->routeIs('admin.statistik.*');

    $adminMenuGroups = [
        [
            'label' => 'Pelajar',
            'icon' => 'pelajar',
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
            'icon' => 'penderma',
            'active' => $adminPendermaActive,
            'items' => [
                [
                    'title' => 'Senarai Penderma',
                    'href' => Route::has('admin.penderma.index') ? route('admin.penderma.index') : '#',
                    'active' => request()->routeIs('admin.penderma.index') || request()->routeIs('admin.penderma.show') || request()->routeIs('admin.penderma.edit'),
                ],
                [
                    'title' => 'Daftar Penderma',
                    'href' => Route::has('admin.penderma.create') ? route('admin.penderma.create') : '#',
                    'active' => request()->routeIs('admin.penderma.create'),
                ],
            ],
        ],
        [
            'label' => 'Analisis',
            'icon' => 'analisis',
            'active' => $adminAnalisisActive,
            'items' => [
                [
                    'title' => 'Sumbangan',
                    'href' => Route::has('admin.sumbangan.index') ? route('admin.sumbangan.index') : '#',
                    'active' => request()->routeIs('admin.sumbangan.*'),
                ],
                [
                    'title' => 'Tabung Bantuan',
                    'href' => Route::has('admin.tabung.index') ? route('admin.tabung.index') : '#',
                    'active' => request()->routeIs('admin.tabung.*'),
                ],
                [
                    'title' => 'Laporan',
                    'href' => Route::has('admin.laporan.index') ? route('admin.laporan.index') : '#',
                    'active' => request()->routeIs('admin.laporan.*') || request()->routeIs('admin.statistik.*'),
                ],
            ],
        ],
    ];
@endphp

<div
    x-data="{
        sidebarOpen: false,
        collapsed: false,
        init() {
            this.collapsed = localStorage.getItem('adminSidebarCollapsed') === 'true';
            this.syncCollapsedClass();
        },
        toggleCollapsed() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('adminSidebarCollapsed', this.collapsed ? 'true' : 'false');
            this.syncCollapsedClass();
            window.dispatchEvent(new Event('resize'));
        },
        expandSidebar() {
            if (window.innerWidth < 768) {
                return;
            }

            this.collapsed = false;
            localStorage.setItem('adminSidebarCollapsed', 'false');
            this.syncCollapsedClass();
            window.dispatchEvent(new Event('resize'));

            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 280);
        },
        handleDashboardClick(event, isActive) {
            if (window.innerWidth < 768) {
                this.sidebarOpen = false;
                return;
            }

            if (!this.collapsed) {
                return;
            }

            this.expandSidebar();

            if (isActive) {
                event.preventDefault();
            }
        },
        syncCollapsedClass() {
            document.documentElement.classList.toggle('admin-sidebar-collapsed', this.collapsed);
        }
    }"
    @keydown.escape.window="sidebarOpen = false"
>
    <header class="topbar">
        <div class="flex min-w-0 items-center gap-4">
            <button
                type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-white/20 text-white transition hover:bg-white/10"
                @click="window.innerWidth < 768 ? sidebarOpen = ! sidebarOpen : toggleCollapsed()"
                :aria-label="collapsed ? 'Buka sidebar admin' : 'Tutup sidebar admin'"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>

            <a href="{{ $adminDashboardHref }}" class="flex min-w-0 items-center gap-3 text-white">
                <div class="brand-mark shrink-0">
                    <svg viewBox="0 0 64 64" fill="none">
                        <circle cx="32" cy="32" r="6" fill="currentColor" />
                        <path d="M32 10L42 16V28L32 34L22 28V16L32 10Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
                        <path d="M14 36L24 42V54L14 60L4 54V42L14 36Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
                        <path d="M50 36L60 42V54L50 60L40 54V42L50 36Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
                        <path d="M32 32L32 34" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        <path d="M27 36L22 42" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        <path d="M37 36L42 42" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="truncate text-base font-bold leading-tight sm:text-lg">
                        eBantuanSiswa UKM
                    </p>
                    <p class="truncate text-xs font-medium text-blue-100">
                        Panel Admin
                    </p>
                </div>
            </a>
        </div>

        <div class="flex items-center">
            @auth
                <x-dropdown align="right" width="w-56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-lg border border-white/15 px-4 py-2 text-sm font-semibold text-slate-100 transition hover:bg-white/10">
                            <span>Admin</span>
                            <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                <path d="M5.293 7.293L10 12l4.707-4.707" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $adminName }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $adminEmail }}</p>
                        </div>

                        <x-dropdown-link :href="Route::has('profile.edit') ? route('profile.edit') : '#'">
                            Maklumat Profil
                        </x-dropdown-link>

                        <x-dropdown-link :href="Route::has('profile.password') ? route('profile.password') : (Route::has('profile.edit') ? route('profile.edit') . '#tukar-kata-laluan' : '#')">
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
    </header>

    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-x-0 bottom-0 top-20 z-30 bg-slate-950/50 md:hidden"
        @click="sidebarOpen = false"
    ></div>

    <aside
        class="sidebar flex -translate-x-full flex-col border-r border-white/10 shadow-2xl transition-transform duration-300 md:translate-x-0"
        :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': ! sidebarOpen }"
        aria-label="Menu admin"
    >
        <div class="profile-section">
            <img
                src="{{ $adminPhoto }}"
                alt="Gambar profil {{ $adminName }}"
                class="mx-auto h-[70px] w-[70px] rounded-full border-2 border-blue-300/70 object-cover shadow-lg"
            >
            <h2 class="mt-3 truncate text-base font-bold text-white">
                {{ $adminName }}
            </h2>
            <p class="truncate text-xs font-medium text-slate-400">
                {{ $adminEmail ?: $adminRole }}
            </p>
        </div>

        <nav class="sidebar-menu flex-1 space-y-2" aria-label="Navigasi admin">
            <a
                href="{{ $adminDashboardHref }}"
                @click="handleDashboardClick($event, {{ $adminDashboardActive ? 'true' : 'false' }})"
                class="{{ $adminDashboardActive ? 'active' : '' }}"
                title="Dashboard"
            >
                <span class="sidebar-menu-main">
                    <span class="sidebar-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 13h7V4H4v9Zm9 7h7V4h-7v16ZM4 20h7v-5H4v5Z" />
                        </svg>
                    </span>
                    <span class="sidebar-label">Dashboard</span>
                </span>
            </a>

            @foreach($adminMenuGroups as $group)
                <div
                    class="menu-group"
                    x-data="{ open: {{ $group['active'] ? 'true' : 'false' }} }"
                    :class="{ 'open': open }"
                >
                    <button
                        type="button"
                        class="{{ $group['active'] ? 'active' : '' }}"
                        @click="
                            if (window.innerWidth < 768) {
                                open = ! open;
                            } else if (collapsed) {
                                expandSidebar();
                                open = true;
                            } else {
                                open = ! open;
                            }
                        "
                        :aria-expanded="open.toString()"
                        title="{{ $group['label'] }}"
                    >
                        <span class="sidebar-menu-main">
                            <span class="sidebar-icon" aria-hidden="true">
                                @if($group['icon'] === 'pelajar')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 11v5c0 1.7 3.1 3 7 3s7-1.3 7-3v-5" />
                                    </svg>
                                @elseif($group['icon'] === 'penderma')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.4-9-9a5 5 0 0 1 8-5.7A5 5 0 0 1 20.9 12c-2 4.6-8.9 9-8.9 9Z" />
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 17v-6M13 17V7M18 17v-3" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h17" />
                                    </svg>
                                @endif
                            </span>
                            <span class="sidebar-label">{{ $group['label'] }}</span>
                        </span>
                        <svg class="sidebar-chevron h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M5.293 7.293L10 12l4.707-4.707" />
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-transition
                        class="submenu mt-1 space-y-1"
                    >
                        @foreach($group['items'] as $item)
                            <a
                                href="{{ $item['href'] }}"
                                @click="sidebarOpen = false"
                                class="{{ $item['active'] ? 'active' : '' }}"
                            >
                                <span class="sidebar-label">{{ $item['title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </aside>
</div>
