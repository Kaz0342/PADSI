@extends('layouts.app')

@section('title', 'Impor Data POS')

@section('content')
<div class="card" style="padding: 30px; text-align:center;">
    <h2 style="margin-bottom:20px;">Import File CSV (POS Kasir Warung)</h2>

    <form action="{{ route('api.pos.import') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <input type="file" name="csv" accept=".csv" required
            style="padding:10px; border:1px solid #ddd; border-radius:8px; width:280px;">

        <br><br>

        <button class="btn" type="submit">Upload</button>
    </form>
</div>
@endsection
