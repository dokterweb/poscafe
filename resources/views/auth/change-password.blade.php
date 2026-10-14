@extends('layouts.app')

@section('content')
<div class="page-wrapper">
   
    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Ganti Password</h3>
                        </div>
                       <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}" enctype="multipart/form-data">
                            @csrf
                        
                            {{-- Tampilkan avatar sekarang --}}
                            <div class="form-group mb-3 text-center">
                                <img src="{{ asset('storage/' .$user->avatar) }}" alt="Avatar"
                                     class="img-thumbnail mb-2" style="width:100px; height:100px; object-fit:cover;">
                                <div>
                                    <label>Ganti Avatar (opsional)</label>
                                    <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror">
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        
                            <hr>
                        
                            <p class="text-muted"><small>
                                Isi bagian di bawah ini hanya jika ingin mengganti email atau password.
                            </small></p>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label>Nama</label>
                                        <input type="name" name="name" value="{{$user->name}}"
                                               class="form-control @error('name') is-invalid @enderror">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Email</label>
                                        <input type="email" name="email"
                                               class="form-control @error('email') is-invalid @enderror">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Password Lama</label>
                                        <input type="password" name="current_password"
                                               class="form-control @error('current_password') is-invalid @enderror">
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Password Baru</label>
                                        <input type="password" name="new_password"
                                               class="form-control @error('new_password') is-invalid @enderror">
                                        @error('new_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label>Konfirmasi Password Baru</label>
                                        <input type="password" name="new_password_confirmation" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                Simpan Perubahan
                            </button>
                        </form>
                        
                       </div>
                                            
                </div>
            </div>
        </div>
    </div>
    
</div>


@endsection

@section('scripts')

<!-- SweetAlert2 Script -->
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        position: 'top-end',
        showConfirmButton: false,
        timer: 1500
    });
</script>
@elseif (session('error'))
   <script>
       Swal.fire({
           icon: 'error',
           title: 'Oops...',
           text: "{{ session('error') }}",
           position: 'top-end',
           showConfirmButton: false,
           timer: 1500
       });
   </script>
@endif

<script>
       $(document).ready(function() {
        // Inisialisasi DataTables
        $('#mytable').DataTable({
            "processing": true,   // Menampilkan loading saat memproses data
            "serverSide": false,  // Tentukan apakah menggunakan server-side processing
            "paging": true,       // Menampilkan pagination
            "lengthChange": false // Menonaktifkan pengaturan jumlah baris per halaman
        });
    });
</script>
@endsection