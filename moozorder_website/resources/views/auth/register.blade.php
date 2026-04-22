<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - MoozOrder</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body {
      background-color: #f5f6fa;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .register-container {
      background: #fff;
      width: 900px;
      border-radius: 12px;
      padding: 40px 50px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }
    .logo {
      display: block;
      margin: 0 auto 25px;
      width: 100px;
    }
    h2 { text-align: center; font-size: 26px; font-weight: 600; margin-bottom: 6px; color: #333; }
    p { text-align: center; font-size: 14px; color: #777; margin-bottom: 30px; }
    form {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px 30px;
    }
    .form-group { display: flex; flex-direction: column; }
    label { font-size: 14px; font-weight: 500; margin-bottom: 6px; color: #333; }
    .form-control {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    .form-control:focus {
      border-color: #ff8000;
      outline: none;
    }
    .form-group-full { grid-column: span 2; }
    .btn {
      width: 100%;
      padding: 12px;
      background-color: #ff8000;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
      grid-column: span 2;
    }
    .btn:hover { background-color: #ff8000; }
    .login-link {
      grid-column: span 2;
      text-align: center;
      font-size: 14px;
      margin-top: 14px;
    }
    .login-link a {
      color: #ff8000;
      text-decoration: none;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
    .error-message {
      grid-column: span 2;
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      padding: 10px 14px;
      border-radius: 6px;
      font-size: 14px;
      margin-bottom: -20px;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <img src="{{ asset('images/Logo_MoozOrder.png') }}" alt="MoozOrder Logo" class="logo" />

    <h2>Create Account</h2>
    <p>Fill in the form to create your MoozOrder account</p>

    <form method="POST" action="{{ url('/register') }}">
      @csrf

      @if($errors->any())
        <div class="error-message">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="form-group">
        <label for="nama">Full Name</label>
        <input type="text" id="nama" name="nama" class="form-control" value="{{ old('nama') }}" required>
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
      </div>

      <div class="form-group">
        <label for="no_hp">Phone Number</label>
        <input type="text" id="no_hp" name="no_hp" class="form-control" value="{{ old('no_hp') }}" required>
      </div>

      <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role" class="form-control" required disabled>
          <option value="user" selected>User</option>
        </select>
        <input type="hidden" name="role" value="user">
      </div>

      <div class="form-group form-group-full">
        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" class="form-control" rows="2" required>{{ old('alamat') }}</textarea>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
      </div>

      <button type="submit" class="btn">Register</button>

      <div class="login-link">
        Already have an account? <a href="{{ url('/login') }}">Sign In</a>
      </div>
    </form>
  </div>
</body>
</html>
