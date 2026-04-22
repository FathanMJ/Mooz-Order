<h1>Selamat Datang Pembeli, {{ auth()->user()->nama }}</h1>
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>