<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login MoozOrder</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
  <style>
    body {
      background-color: #f4f7fa;
      font-family: 'Roboto', sans-serif;
      margin: 0;
      padding: 0;
    }

    .login-wrapper {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      padding: 1rem;
    }

    .login-box {
      background-color: #fff;
      width: 100%;
      max-width: 500px;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.3s ease-in-out;
    }

    .login-box:hover {
      transform: translateY(-6px);
    }

    .login-image {
      background-color: #fff;
      text-align: center;
      padding: 2rem 1rem 1rem;
    }

    .login-image img {
      max-width: 120px;
      opacity: 0.95;
    }

    .login-form {
      padding: 2rem 2rem 2.5rem;
    }

    .login-form h3 {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: #333;
    }

    .form-label {
      font-weight: 500;
      font-size: 0.95rem;
      margin-bottom: 6px;
      color: #444;
    }

    .form-control {
      border-radius: 8px;
      padding: 0.75rem;
      font-size: 0.95rem;
      border: 1px solid #ccc;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: #ff8000;
      box-shadow: 0 0 6px rgba(255, 128, 0, 0.4);
    }

    .btn-orange {
      background-color: #ff8000;
      color: #fff;
      font-weight: 600;
      font-size: 0.95rem;
      border-radius: 8px;
      padding: 0.75rem;
      border: none;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .btn-orange:hover {
      background-color: #e67300;
      transform: scale(1.03);
    }

    .mt-3 a {
      color: #ff8000;
      text-decoration: none;
      font-weight: 500;
    }

    .mt-3 a:hover {
      text-decoration: underline;
    }

    .alert {
      font-size: 0.95rem;
      padding: 0.75rem;
      border-radius: 8px;
    }

    @media (max-width: 576px) {
      .login-box {
        padding: 1rem;
      }

      .login-image img {
        max-width: 90px;
      }

      .login-form h3 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

  <div class="login-wrapper">
    <div class="login-box">
      <!-- Gambar Logo -->
      <div class="login-image">
        <img src="{{ asset('images/Logo_MoozOrder.png') }}" alt="Logo MoozOrder">
      </div>

      <!-- Form Login -->
      <div class="login-form">
        <h3>Login ke MoozOrder</h3>

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ url('login') }}">
          @csrf
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required placeholder="Masukkan email">
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required placeholder="Masukkan password">
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-orange">Login</button>
          </div>

          <div class="mt-3 text-center">
            <span>Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></span>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
