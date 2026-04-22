@extends('layout.v_template')
@section('page')
@section('content')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0" style="border-radius: 1.5rem;">
                    <div class="card-body text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <h2 class="fw-bold mb-2" style="text-transform: capitalize; color: #222;">
                                Selamat Datang Admin MOOZORDER !!!
                            </h2>
                            <h2 class="fw-bold mb-2" style="text-transform: capitalize; color: #222;">

                            </h2>
                            <h3 class="mb-0" style="text-transform: capitalize; color: #f89f21;">
                                {{ ucwords(strtolower(auth()->user()->nama)) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
