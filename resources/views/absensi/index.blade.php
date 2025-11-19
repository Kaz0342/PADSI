@extends('layout')
@section('page_title','Absensi')

@section('content')
<div style="display:flex;flex-direction:column;gap:18px">

  <div style="display:flex;justify-content:space-between;align-items:center">
    <h3>Absensi Hari Ini</h3>
    <form method="POST" action="{{ route('absensi.checkin') }}">
      @csrf
      <button class="btn">Check-in Sekarang</button>
    </form>
  </div>

  <div style="background:#fff;padding:16px;border-radius:12px">
    <div style="margin-bottom:10px">Status: <strong>{{ $status }}</strong></div>

    <table style="width:100%;border-collapse:collapse">
      <thead style="background:#f3f4f6">
        <tr><th>#</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Status</th></tr>
      </thead>
      <tbody>
        @forelse($segments as $i => $s)
          <tr>
            <td style="padding:8px">{{ $i+1 }}</td>
            <td style="padding:8px">{{ $s->check_in_at }}</td>
            <td style="padding:8px">{{ $s->check_out_at ?? '-' }}</td>
            <td style="padding:8px">{{ $s->status_kehadiran ?? $s->status ?? '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="4" style="padding:12px">Belum ada absensi</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
@endsection
