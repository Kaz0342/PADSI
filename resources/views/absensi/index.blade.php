@extends('layouts.app')
@section('title', 'Riwayat Absensi Saya')

@section('content')

<style>
.absensi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.absensi-header h2 { 
    margin: 0; 
    font-size: 22px; 
    color: #1f2937; 
    font-weight: 700; 
}

.card-history {
    background: var(--card);
    border-radius: var(--radius);
    padding: 0;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}
.history-table { 
    width: 100%; 
    border-collapse: collapse; 
    font-size: 14px;
}
.history-table th {
    background: #f9fafb;
    padding: 12px 18px;
    text-align: left;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    font-size: 12px;
}
.history-table td {
    padding: 12px 18px;
    border-bottom: 1px solid #e5e7eb;
    color: #374151;
}
.history-table tr:last-child td { border-bottom: none; }
.history-table tbody tr:hover { background-color: #f9fafb; }

.absensi-date { font-weight: 600; }
.status-badge { padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; display: inline-block; }
.status-hadir { background: #dcfce7; color: #166534; }
.status-terlambat { background: #fef9c3; color: #854d0e; }
.status-alpha { background: #fee2e2; color: #991b1b; }

.riwayat-group { 
    display: flex; 
    gap: 15px; 
    align-items: center; 
    margin-bottom: 20px;
}

</style>

<div class="absensi-header">
    <h2>Riwayat Absensi Saya</h2>
    <a href="{{ route('absensi.pengganti.form') }}" class="btn">
        <i class="fa-solid fa-user-plus"></i> Ajukan Absensi Pengganti
    </a>
</div>

{{-- Filter Bulan (Jika nanti dibutuhkan) --}}
<div class="riwayat-group">
    <div style="font-weight: 600;">Riwayat Bulan Ini:</div>
    <select onchange="window.location.href = this.value" style="width:auto; padding: 6px 10px; border-radius: 6px;">
        {{-- Logika Blade untuk generate bulan (Contoh: Menampilkan 3 bulan terakhir) --}}
        @for($i=0; $i<3; $i++)
            @php
                $bulan = \Carbon\Carbon::now()->subMonths($i);
            @endphp
            <option value="?month={{ $bulan->month }}&year={{ $bulan->year }}" 
                    {{ request('month', now()->month) == $bulan->month ? 'selected' : '' }}>
                {{ $bulan->isoFormat('MMMM YYYY') }}
            </option>
        @endfor
    </select>
</div>


<div class="card-history">
    <div class="table-responsive">
        <table class="history-table">
            <thead>
                <tr>
                    <th width="15%">Tanggal</th>
                    <th width="15%">Masuk</th>
                    <th width="15%">Keluar</th>
                    <th width="15%">Durasi</th>
                    <th width="15%">Status Kehadiran</th>
                    <th width="25%">Catatan</th>
                </tr>
            </thead>
            <tbody>
                {{-- ASUMSI: $records di-pass dari AbsensiController::index, berisi riwayat bulan yang dipilih --}}
                @forelse($records ?? [] as $record)
                    <tr>
                        <td class="absensi-date">{{ \Carbon\Carbon::parse($record->tanggal)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->check_in_at)->format('H:i') }}</td>
                        <td>{{ $record->check_out_at ? \Carbon\Carbon::parse($record->check_out_at)->format('H:i') : '-' }}</td>
                        <td>
                            @if($record->check_in_at && $record->check_out_at)
                                @php
                                    $start = \Carbon\Carbon::parse($record->check_in_at);
                                    $end = \Carbon\Carbon::parse($record->check_out_at);
                                    $durasi = $start->diff($end)->format('%h jam, %i menit');
                                @endphp
                                {{ $durasi }}
                            @else
                                Sedang Aktif
                            @endif
                        </td>
                        <td>
                            @php
                                $statusClass = strtolower($record->status_kehadiran ?? 'alpha');
                                $statusText = ucfirst($statusClass);
                            @endphp
                            <span class="status-badge status-{{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td>{{ $record->catatan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px; color:#9ca3af;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size:24px; margin-bottom:10px;"></i>
                            Belum ada riwayat absensi pada bulan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection