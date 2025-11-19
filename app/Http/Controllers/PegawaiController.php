<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawai = Pegawai::all();
        return view('pegawai.index', compact('pegawai'));
    }

    public function create()
    {
        return view('pegawai.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'role' => 'required',
            'status' => 'required',
    ]);

    \App\Models\Pegawai::create([
        'nama' => $request->nama,
        'role' => $request->role,
        'status' => $request->status,
    ]);
    return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        return view('pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'role' => 'required',
            'status' => 'required',
    ]);

        $pegawai = \App\Models\Pegawai::findOrFail($id);
        $pegawai->update([
            'nama' => $request->nama,
            'role' => $request->role,
            'status' => $request->status,
    ]);

    return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $pegawai = \App\Models\Pegawai::findOrFail($id);
        $pegawai->delete();

    return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil dihapus!');
    }
}
