<header class="navbar navbar-expand-md d-print-none" >
    <div class="container-xl">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
        <a href=".">
          {{-- <img src="./static/logo.svg" width="110" height="32" alt="Tabler" class="navbar-brand-image"> --}}
          <i class="fab fa-apple fa-lg"></i>
        </a>
      </h1>
      <div class="navbar-nav flex-row order-md-last">
        <div class="d-none d-md-flex">
          <a href="?theme=dark" class="nav-link px-0 hide-theme-dark" title="Enable dark mode" data-bs-toggle="tooltip"
       data-bs-placement="bottom">
            <!-- Download SVG icon from http://tabler-icons.io/i/moon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" /></svg>
          </a>
          <a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip"
       data-bs-placement="bottom">
            <!-- Download SVG icon from http://tabler-icons.io/i/sun -->
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>
          </a>
          
        </div>
        <div class="nav-item dropdown">
          <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
            @auth
              <span class="avatar avatar-sm" style="background-image: url('{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/avatar/default-avatar.png') }}')"></span>
            @endauth


            <div class="d-none d-xl-block ps-2">
              <div>{{auth()->user()->name}}</div>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <a href="{{ route('profile.index') }}" class="dropdown-item">Profile</a>
            <div class="dropdown-divider"></div>
              <a href="{{route('password.change')}}" class="dropdown-item">Settings</a>
              <form action="{{route('logout')}}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item">Logout</button>
              </form>
          </div>
        </div>
      </div>
      @role('admin')
      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="{{route('admin.dashboard')}}" >
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="fas fa-home"></i>
                </span>
                <span class="nav-link-title">
                  Home
                </span>
              </a>
            </li>
            
            <li class="nav-item">
              <a class="nav-link" href="{{route('stock-movements.index')}}">
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="nav-icon fas fa-cog"></i>
                </span>
                <span class="nav-link-title">
                  Stok
                </span>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="nav-icon fas fa-cog"></i>
                </span>
                <span class="nav-link-title">Transaksi</span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item {{request()->routeIs('pos.index')?'active':''}}" href="{{route('pos.index')}}">
                  POS
                </a>
                 <a class="dropdown-item {{request()->routeIs('pos.mejas.index')?'active':''}}" href="{{route('pos.mejas.index')}}">
                  Meja
                </a>
                 <a class="dropdown-item {{request()->routeIs('pos.laporan-penjualan.index')?'active':''}}" href="{{route('pos.laporan-penjualan.index')}}">
                  Laporan
                </a>
                <a class="dropdown-item {{request()->routeIs('consignment-reports.index')?'active':''}}" href="{{route('consignment-reports.index')}}">
                  Laporan Mitra
                </a>
                <a class="dropdown-item {{request()->routeIs('consignment-settlements.index')?'active':''}}" href="{{route('consignment-settlements.index')}}">
                  Bayar Mitra
                </a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="nav-icon fas fa-cog"></i>
                </span>
                <span class="nav-link-title">Mitra</span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item {{request()->routeIs('consignment-reports.index')?'active':''}}" href="{{route('consignment-reports.index')}}">
                  Laporan Mitra
                </a>
                <a class="dropdown-item {{request()->routeIs('consignment-settlements.index')?'active':''}}" href="{{route('consignment-settlements.index')}}">
                  Bayar Mitra
                </a>
                <a class="dropdown-item {{request()->routeIs('consignment-returns.index')?'active':''}}" href="{{route('consignment-returns.index')}}">
                  Return Barang
                </a>
              </div>
            </li>
             <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="nav-icon fas fa-cog"></i>
                </span>
                <span class="nav-link-title">Adjustment</span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item {{request()->routeIs('stock-adjustments.index')?'active':''}}" href="{{route('stock-adjustments.index')}}">
                  Stock Adjustment
                </a>
                 <a class="dropdown-item {{request()->routeIs('stock-adjustments.create')?'active':''}}" href="{{route('stock-adjustments.create')}}">
                  Adjustment Baru
                </a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="nav-icon fas fa-cog"></i>
                </span>
                <span class="nav-link-title">Pembelian</span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item {{request()->routeIs('purchases.index')?'active':''}}" href="{{route('purchases.index')}}">
                  Pembelian
                </a>
                 <a class="dropdown-item {{request()->routeIs('purchases.report')?'active':''}}" href="{{route('purchases.report')}}">
                  Laporan
                </a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="nav-icon fas fa-cog"></i>
                </span>
                <span class="nav-link-title">Master</span>
              </a>
              <div class="dropdown-menu">
                <a class="dropdown-item {{request()->routeIs('categories')?'active':''}}" href="{{route('categories')}}">
                  Kategori
                </a>
                 <a class="dropdown-item {{request()->routeIs('suppliers')?'active':''}}" href="{{route('suppliers')}}">
                  Supplier
                </a>
                 <a class="dropdown-item {{request()->routeIs('products.index')?'active':''}}" href="{{route('products.index')}}">
                  Produk
                </a>
                 <a class="dropdown-item {{request()->routeIs('customers.index')?'active':''}}" href="{{route('customers.index')}}">
                  Customer
                </a>
                <a class="dropdown-item {{request()->routeIs('option-groups.index')?'active':''}}" href="{{route('option-groups.index')}}">
                  Tambahan
                </a>
                 <a class="dropdown-item {{request()->routeIs('mejas.index')?'active':''}}" href="{{route('mejas.index')}}">
                  Meja
                </a>
              </div>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{ route('backup.index') }}" >
                <span class="nav-link-icon d-md-none d-lg-inline-block">
                  <i class="nav-icon fas fa-cog"></i>
                </span>
                <span class="nav-link-title">
                  Backup & Restore
                </span>
              </a>
            </li>
          </ul>
        </div>
      </div>
      @endrole
    
    </div>
  </header>