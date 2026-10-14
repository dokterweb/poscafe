<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>
    <!-- CSS files -->
    <link href="{{asset('tabler')}}/dist/css/tabler.min.css?1684106062" rel="stylesheet"/>
    <link href="{{asset('tabler')}}/dist/css/tabler-vendors.min.css?1684106062" rel="stylesheet"/>
    <link href="{{asset('tabler')}}/dist/css/demo.min.css?1684106062" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
  
    @stack('styles')
    <style>
      @import url('https://rsms.me/inter/inter.css');
      :root {
      	--tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
      }
      body {
      	font-feature-settings: "cv03", "cv04", "cv11";
      }
    </style>
  </head>
  <body >
    <script src="{{asset('tabler')}}/dist/js/demo-theme.min.js?1684106062"></script>
    <div class="page">
      <!-- Navbar -->
      @include('layouts.header')

      @yield('content')
      <footer class="footer footer-transparent d-print-none">
        <div class="container-xl">
          <div class="row text-center align-items-center flex-row-reverse">
            <div class="col-lg-auto ms-lg-auto">
              <ul class="list-inline list-inline-dots mb-0">
                <li class="list-inline-item"><a href="https://dokterweb.com" target="_blank" class="link-secondary" rel="noopener">DokterWeb</a></li>
              
              </ul>
            </div>
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
              <ul class="list-inline list-inline-dots mb-0">
                <li class="list-inline-item">
                  Copyright &copy; 2026
                  <a href="." class="link-secondary">RudyPOS</a>.
                  All rights reserved.
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
   
    <!-- Tabler Core -->
    <script src="{{asset('tabler')}}/dist/js/tabler.min.js?1684106062" defer></script>
    <script src="{{asset('tabler')}}/dist/js/demo.min.js?1684106062" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    @yield('scripts')
    @if (session('success'))
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif

    @if (session('error'))
    <script>
        Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonText: 'OK'
          });
    </script>
    @endif

  </body>
</html>