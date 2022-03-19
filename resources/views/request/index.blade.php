@extends('layout.main')
@section('content')
    <div class="section-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table-bordered table-md table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Barang</th>
                                <th>Deskripsi</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Tanggal Dibuat</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>
                                        @if ($item->status == 'Diterima')
                                            <div class="badge badge-success">{{ $item->status }}</div>
                                        @elseif ($item->status == 'Ditolak')
                                            <div class="badge badge-danger">{{ $item->status }}</div>
                                        @else
                                            <div class="badge badge-warning">{{ $item->status }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $item->created_at }}</td>
                                    <td>
                                        @if (auth()->user()->role == 'super_user' && $item->status == 'Menunggu Persetujuan')
                                            <button class="btn btn-primary btn-review" data-id="{{ $item->id }}">Review</button>
                                        @else
                                            <button class="btn btn-primary btn-detail" data-id="{{ $item->id }}">Detail</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                Data Masih Kosong
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

<div class="modal fade show" tabindex="-1" role="dialog" id="modalReview">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReviewTitle">Review</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formReview" data-id="">
                    <div class="form-group">
                        <label>Nama Barang</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="namaBarang" readonly placeholder="nama barang">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Barang</label>
                        <div class="input-group">
                            <textarea class="form-control h-25" id="deskripsiBarang" rows="3" readonly></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="statusBarang" disabled="true">
                            <option>Diterima</option>
                            <option>Menunggu Persetujuan</option>
                            <option>Ditolak</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Feedback</label>
                        <div class="input-group">
                            <textarea class="form-control h-25" id="feedbackRequest" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-whitesmoke br">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="btnSubmit">Submit Review</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        $(document).ready(function() {
            $('.btn-detail').on('click', function(event) {
                var id = $(this).data('id');
                $('#modalReview').modal('show');
                $('#modalReview').toggleClass('modal-progress');
                $.ajax({
                    url: `request/${id}`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'GET',
                    success: function(data) {
                        $('#modalReviewTitle').html(`Detail ${data.name}`)
                        $('#namaBarang').val(data.name);
                        $('#deskripsiBarang').val(data.description);
                        $('#statusBarang').val(data.status);
                        $('#feedbackRequest').val(data.feedback)
                        $('#statusBarang').attr("disabled", true);
                        $('#feedbackRequest').attr("readonly", true);
                        $('#modalReview').toggleClass('modal-progress');
                        $('#btnSubmit').hide();
                    },
                    error: function() {
                        $('#modalReview').toggleClass('modal-progress');
                        alert('Internal Server Error');
                    }
                });
            });
            $('.btn-review').on('click', function(event) {
                var id = $(this).data('id');
                $('#modalReview').modal('show');
                $('#modalReview').toggleClass('modal-progress');
                $('#formReview').attr('action', `request/${id}/edit`);
                $('#formReview').attr('data-id', id);
                $.ajax({
                    url: `request/${id}/edit`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'GET',
                    success: function(data) {
                        $('#modalReviewTitle').html(`Review ${data.name}`)
                        $('#namaBarang').val(data.name);
                        $('#deskripsiBarang').val(data.description);
                        $('#statusBarang').val(data.status);
                        $('#statusBarang').attr("disabled", false);
                        $('#feedbackRequest').val(data.feedback)
                        $('#feedbackRequest').attr("readonly", false);
                        $('#modalReview').toggleClass('modal-progress');
                        $('#btnSubmit').show();
                    },
                    error: function() {
                        $('#modalReview').toggleClass('modal-progress');
                        alert('Internal Server Error');
                    }
                });
            });
            $('#btnSubmit').click(function(e) {
                e.preventDefault();
                var id = $('#formReview').data('id');
                $('#modalReview').toggleClass('modal-progress');
                $.ajax({
                    url: `request/${id}`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: 'POST',
                    data: {
                        status: $('#statusBarang').val(),
                        feedback: $('#feedbackRequest').val(),
                        _method: "PATCH"
                    },
                    success: function(data) {
                        $('#modalReview').toggleClass('modal-progress');
                        location.reload();
                    },
                    error: function(data) {
                        $('#modalReview').toggleClass('modal-progress');
                        alert('Internal Server Error');
                    }
                });
            });
        });
    </script>
@endpush