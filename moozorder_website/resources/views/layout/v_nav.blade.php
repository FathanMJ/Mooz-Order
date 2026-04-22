<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
    <div class="sidenav-header d-flex flex-column align-items-center justify-content-center" style="padding-top: 20px; padding-bottom: 20px;">
      <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0 p-0 d-flex justify-content-center align-items-center" target="_blank" style="width:100%;">
        <img src="{{ asset('images/Logo_MoozOrder.png') }}" alt="Logo Mooz Order" style="max-width: 90%; height: auto; max-height: 120px; object-fit:contain; display:block; margin:0 auto;">
      </a>
    </div>
    {{-- <hr class="horizontal dark mt-0 mb-2"> --}}
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('admin.dashboard') }}">
            <i class="material-symbols-rounded opacity-5">dashboard</i>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('pengguna.*') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('pengguna.index') }}">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Kelola Pengguna</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('produk.*') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('produk.index') }}">
            <i class="material-symbols-rounded opacity-5">table_view</i>
            <span class="nav-link-text ms-1">Kelola Produk</span>
          </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.kategori.*') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('admin.kategori.index') }}">
                <i class="material-symbols-rounded opacity-5">category</i>
                <span class="nav-link-text ms-1">Kelola Kategori</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.pesanan.*') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('admin.pesanan.index') }}">
              <i class="material-symbols-rounded opacity-5">table_view</i>
              <span class="nav-link-text ms-1">Kelola Pesanan</span>
            </a>
        </li>
        {{-- <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('admin.laporan.keuangan') }}">
              <i class="material-symbols-rounded opacity-5">table_view</i>
              <span class="nav-link-text ms-1">Laporan Keuangan</span>
            </a>
        </li> --}}
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active bg-gradient-dark text-white' : 'text-dark' }}" href="{{ route('admin.laporan.penjualan') }}">
              <i class="material-symbols-rounded opacity-5">table_view</i>
              <span class="nav-link-text ms-1">Laporan Penjualan</span>
            </a>
        </li>
        {{-- <li class="nav-item">
          <a class="nav-link text-dark" href="../pages/billing.html">
            <i class="material-symbols-rounded opacity-5">receipt_long</i>
            <span class="nav-link-text ms-1">Billing</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="../pages/virtual-reality.html">
            <i class="material-symbols-rounded opacity-5">view_in_ar</i>
            <span class="nav-link-text ms-1">Virtual Reality</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="../pages/rtl.html">
            <i class="material-symbols-rounded opacity-5">format_textdirection_r_to_l</i>
            <span class="nav-link-text ms-1">RTL</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="../pages/notifications.html">
            <i class="material-symbols-rounded opacity-5">notifications</i>
            <span class="nav-link-text ms-1">Notifications</span>
          </a>
        </li> --}}
      </ul>
    </div>
  </aside>
