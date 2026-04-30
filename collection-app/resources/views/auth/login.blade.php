@extends('layout', ['title' => 'Admin Login'])

@section('content')
    <div class="card" style="max-width:520px">
        <h1>Admin Login</h1>
        <p class="muted">เฉพาะเจ้าของสำหรับเพิ่มของสะสม</p>
        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <label>Email</label>
            <input class="input" type="email" name="email" required>
            <label>Password</label>
            <input class="input" type="password" name="password" required>
            <button class="btn" type="submit">Login</button>
        </form>
    </div>
@endsection
