<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Pegawai;
use App\Models\AbsensiPengganti;
use App\Models\PosTransactionTemp;
use Illuminate\Http\JsonResponse;

class AbsensiController extends Controller
{
    /* ============================================================
       HITUNG STATUS KEHADIRAN
    ============================================================ */
    protected function determineStatus($checkInAt, $shiftStartTime)
    {
        $checkIn = Carbon::parse($checkInAt);
        $today   = $checkIn->toDateString();
        $start   = Carbon::parse("$today $shiftStartTime");

        $diff = $start->diffInMinutes($checkIn, false);

        if ($diff <= 0)  return 'hadir';
        if ($diff <= 60) return 'terlambat';
        return 'alpha';
    }


    /* ============================================================
       HALAMAN ABSENSI PEGAWAI
    ============================================================ */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) return redirect()->route('login');

        if ($user->role === 'owner') {
            return redirect()->route('dashboard')->with('error','Owner tidak bisa membuka halaman absensi pegawai.');
        }

        $pegawai = $user->pegawai;
        if (!$pegawai) {
            return redirect()->route('dashboard')->with('error','Akun Anda tidak terhubung ke data pegawai.');
        }

        $today = Carbon::today()->toDateString();

        $jadwal = Jadwal::with('shift')
            ->where('pegawai_id', $pegawai->id)
            ->where('tanggal', $today)
            ->first();

        $segments = Absensi::where('pegawai_id', $pegawai->id)
            ->where('tanggal', $today)
            ->orderBy('check_in_at')
            ->get();

        /* -------- STATUS -------- */
        if ($segments->count() > 0) {

            $status = strtolower($segments->first()->status_kehadiran);

        } else {

            if ($jadwal && $jadwal->shift) {
                $start  = Carbon::parse($jadwal->shift->start_time);
                $limit  = Carbon::today()->setTime($start->hour, $start->minute)->addMinutes(60);
                $status = now('Asia/Jakarta')->gt($limit) ? 'alpha' : 'Belum Check-in';
            } else {
                $status = 'Belum Check-in';
            }
        }

        return view('absensi.index', [
            'segments' => $segments,
            'jadwal'   => $jadwal,
            'status'   => $status,
            'pegawai'  => $pegawai
        ]);
    }


    /* ============================================================
       CHECK-IN
    ============================================================ */
    public function checkIn(Request $request)
    {
        $pegawai = auth()->user()->pegawai;
        if (!$pegawai) return back()->with('error','Data pegawai tidak ditemukan.');

        $today = Carbon::today()->toDateString();
        $now   = now('Asia/Jakarta');

        $active = Absensi::where('pegawai_id',$pegawai->id)
            ->where('tanggal',$today)
            ->whereNull('check_out_at')
            ->first();

        if ($active) return back()->with('error','Anda sudah check-in.');

        $done = Absensi::where('pegawai_id',$pegawai->id)
            ->where('tanggal',$today)
            ->whereNotNull('check_out_at')
            ->exists();

        if ($done)
            return back()->with('error','Sesi kerja hari ini sudah selesai.');

        $abs = Absensi::create([
            'pegawai_id' => $pegawai->id,
            'tanggal'    => $today,
            'check_in_at'=> $now,
            'lokasi_lat' => $request->lat,
            'lokasi_long'=> $request->long,
            'location_info'=> $request->location_info,
            'status_kehadiran'=> 'hadir'
        ]);

        $jadwal = Jadwal::with('shift')
            ->where('pegawai_id',$pegawai->id)
            ->where('tanggal',$today)
            ->first();

        /* ---- Tidak Ada Jadwal → Pengganti ---- */
        if (!$jadwal) {
            Session::put('absensi_id',$abs->id);
            return redirect()->route('absensi.pengganti.form')
                ->with('info','Anda tidak terjadwal — pilih karyawan yang Anda gantikan.');
        }

        /* ---- Tentukan Status ---- */
        $status = $this->determineStatus($now, $jadwal->shift->start_time);
        $abs->update(['status_kehadiran'=>$status]);

        return redirect()->route('dashboard')->with('success','Check-in berhasil.');
    }


    /* ============================================================
       FORM PENGGANTI
    ============================================================ */
    public function showPenggantiForm()
    {
        $absId = Session::get('absensi_id');
        if (!$absId)
            return redirect()->route('dashboard')->with('error','Absensi pengganti tidak ditemukan.');

        $today = Carbon::today()->toDateString();

        $jadwalToday = Jadwal::where('tanggal',$today)->pluck('pegawai_id')->toArray();

        $eligible = Pegawai::whereIn('id',$jadwalToday)
            ->where('id','!=',auth()->user()->pegawai->id)
            ->get();

        return view('absensi.pengganti', compact('eligible'));
    }


    /* ============================================================
       SIMPAN PENGGANTI
    ============================================================ */
    public function storePengganti(Request $request)
    {
        $request->validate([
            'menggantikan_id' => 'required|exists:pegawai,id'
        ]);

        $abs = Absensi::findOrFail(Session::get('absensi_id'));

        DB::transaction(function() use ($request,$abs) {

            $log = AbsensiPengganti::create([
                'pengganti_id'  => $abs->pegawai_id,
                'digantikan_id' => $request->menggantikan_id,
                'tanggal'       => $abs->tanggal,
                'absensi_id'    => $abs->id
            ]);

            $abs->update([
                'status_kehadiran'=>'pengganti',
                'absensi_pengganti_id'=>$log->id
            ]);

        });

        Session::forget('absensi_id');

        return redirect()->route('absensi.index')->with('success','Penggantian disimpan.');
    }


    /* ============================================================
       CHECK-OUT
    ============================================================ */
    public function checkOut(Request $request)
    {
        $pegawai = auth()->user()->pegawai;
        $segment = Absensi::where('pegawai_id',$pegawai->id)
            ->whereNull('check_out_at')
            ->first();

        if (!$segment)
            return back()->with('error','Tidak ada sesi aktif.');

        $now = now('Asia/Jakarta');

        $jadwal = Jadwal::with('shift')
            ->where('pegawai_id',$pegawai->id)
            ->where('tanggal',$segment->tanggal)
            ->first();

        if ($jadwal) {
            $end = Carbon::parse($segment->tanggal.' '.$jadwal->shift->end_time);

            if ($now->lt($end)) {
                $request->validate(['alasan'=>'required'],[
                    'alasan.required' => 'Anda check-out dini. Alasan wajib.'
                ]);
            }
        }

        $segment->update([
            'check_out_at'=>$now,
            'catatan'=>$request->alasan
        ]);

        return back()->with('success','Check-out berhasil.');
    }


    /* ============================================================
       OWNER — HALAMAN REKAP
    ============================================================ */
    public function rekap()
    {
        return view('absensi.rekap');
    }


    /* ============================================================
       OWNER — KALENDER JSON
    ============================================================ */
    public function getRekapCalendarJson(Request $req): JsonResponse
    {
        $month = intval($req->month ?? now()->month);
        $year  = intval($req->year  ?? now()->year);

        Carbon::setLocale('id');

        $first = Carbon::create($year,$month,1);
        $days  = $first->daysInMonth;
        $idx   = $first->dayOfWeek;

        $absensi = Absensi::whereMonth('tanggal',$month)
            ->whereYear('tanggal',$year)
            ->get()
            ->groupBy(fn($a)=>Carbon::parse($a->tanggal)->toDateString());

        $posPending = PosTransactionTemp::whereMonth('tanggal',$month)
            ->whereYear('tanggal',$year)
            ->pluck('tanggal')
            ->map(fn($x)=>Carbon::parse($x)->toDateString())
            ->toArray();

        $calendar = [];

        /* filler sebelum bulan */
        for ($i=0;$i<$idx;$i++){
            $date = $first->copy()->subDays($idx-$i);
            $calendar[] = [
                'label'=>$date->day,
                'date'=>$date->toDateString(),
                'dots'=>[],
                'summary'=>'',
                'isCurrentMonth'=>false,
                'isToday'=>false
            ];
        }

        /* tanggal bulan ini */
        for ($d=1;$d<=$days;$d++){
            $dateObj = Carbon::create($year,$month,$d);
            $date    = $dateObj->toDateString();

            $dots=[]; $summary="0 data";

            if ($absensi->has($date)){
                $data = $absensi[$date];

                $hadir   = $data->whereIn('status_kehadiran',['hadir','pengganti'])->count();
                $summary = "$hadir hadir (".$data->count()." sesi)";

                $statuses = $data->pluck('status_kehadiran')->toArray();
                if (in_array('alpha',$statuses))      $dots[]='red';
                if (in_array('terlambat',$statuses))  $dots[]='yellow';
                if (in_array('hadir',$statuses) || in_array('pengganti',$statuses)) 
                    $dots[]='green';
            }

            $calendar[]=[
                'label'=>$d,
                'date'=>$date,
                'dots'=>array_slice($dots,0,3),
                'summary'=>$summary,
                'isCurrentMonth'=>true,
                'isToday'=>Carbon::today('Asia/Jakarta')->isSameDay($dateObj),
                'pos_pending'=>in_array($date,$posPending)
            ];
        }

        /* filler setelah bulan */
        while (count($calendar)%7 !==0){
            $last = Carbon::create($year,$month,$days)->addDays(count($calendar)%7);
            $calendar[]=[
                'label'=>$last->day,
                'date'=>$last->toDateString(),
                'dots'=>[],
                'summary'=>'',
                'isCurrentMonth'=>false,
                'isToday'=>false
            ];
        }

        return response()->json([
            'calendar'=>$calendar,
            'pos_pending'=>$posPending,
            'currentMonthName'=>$first->isoFormat('MMMM YYYY'),
            'currentMonth'=>$month,
            'currentYear'=>$year
        ]);
    }


    /* ============================================================
       DETAIL ABSENSI PER TANGGAL
    ============================================================ */
    public function getRekapDetailJson(Request $req): JsonResponse
    {
        $req->validate(['date'=>'required|date']);
        $date = $req->date;

        $list = Absensi::with('pegawai')
            ->where('tanggal',$date)
            ->get();

        $summary = [
            'hadir'=>0,'terlambat'=>0,'alpha'=>0,'pengganti'=>0
        ];

        $rows = $list->map(function($a) use (&$summary){
            $st = strtolower($a->status_kehadiran);
            if(isset($summary[$st])) $summary[$st]++;

            return [
                'nama'=>$a->pegawai->nama ?? '-',
                'posisi'=>$a->pegawai->jabatan ?? '-',
                'status_kehadiran'=>$st,
                'check_in'=>$a->check_in_at ? Carbon::parse($a->check_in_at)->format('H:i'):null,
                'check_out'=>$a->check_out_at? Carbon::parse($a->check_out_at)->format('H:i'):null,
                'catatan'=>$a->catatan
            ];
        });

        return response()->json([
            'date_formatted'=>Carbon::parse($date)->isoFormat('dddd, D MMMM YYYY'),
            'summary'=>$summary,
            'rows'=>$rows
        ]);
    }

}
