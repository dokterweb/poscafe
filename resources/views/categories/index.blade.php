@extends('layouts.app')

@section('content')
<div class="page-wrapper">
   
    <!-- Page body -->
    <div class="page-body">
      <div class="container-xl">
        <div class="row row-cards">
          <div class="col-lg-4">
            <form class="card" method="POST" action="{{route('categories.store')}}">
              @csrf
              <div class="card-header">
                <h3 class="card-title">Input Data</h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label">Kategori</label>
                  <input type="text" name="name" class="form-control">
                </div>
              </div>
              <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
            </form>
          </div>

          <div class="col-lg-8">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Data Kategori</h3>
              </div>
              <div class="table-responsive">
                <table class="table table-vcenter card-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Nama Kategori</th>
                      <th class="w-1">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($categories as $p)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$p->name}} </td>
                                <td class="d-flex align-items-center" style="gap: 5px;">
                                   <a href="{{route('categories.edit',$p->id)}}" class="btn btn-sm btn-info"><i class="far fa-edit"></i>Edit</a>
                                    <form method="POST" action="{{ route('categories.destroy', $p->id) }}" style="display: inline;" id="delete-form-{{ $p->id }}">
                                      @csrf
                                      @method('DELETE')
                                      <button type="button" class="btn btn-sm btn-danger" onclick="deleteConfirmation({{ $p->id }})">
                                          <i class="fas fa-trash-alt"></i> Hapus
                                      </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                        <tr>
                            <td colspan="3">No Data</td>
                        </tr>
                        @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function deleteConfirmation(id) {
        // SweetAlert2 konfirmasi
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Kirimkan form untuk menghapus data jika dikonfirmasi
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection