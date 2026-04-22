<?php

namespace App\Http\Controllers\Api;

use App\Models\M_pengguna;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;

class ApiAuthController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct()
    {
        $this->middleware('auth:api')->except(['login', 'register', 'sendOtp', 'resetPassword']);
    }

    public function register(RegisterRequest $request)
    {
        try {
            $data = $request->validated();

            // Remove password_confirmation from data
            unset($data['password_confirmation']);

            // Set default values if not provided
            $data['no_hp'] = $data['no_hp'] ?? '-';
            $data['alamat'] = $data['alamat'] ?? '-';
            $data['role'] = $data['role'] ?? 'user';

            // Create user
            $user = M_pengguna::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'password' => Hash::make($data['password']),
                'role' => $data['role']
            ]);

            // Generate token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat registrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();

            Log::info('Login attempt received credentials: ' . json_encode($credentials)); // Log received credentials

            // Manual check to see if user exists with the given email
            $user = M_pengguna::where('email', $credentials['email'])->first();

            if (!$user) {
                 Log::warning('Login attempt failed: User with email ' . $credentials['email'] . ' not found.');
                 return response()->json([
                     'status' => false,
                     'message' => 'Email atau password salah'
                 ], 401);
            }

            // Manual password verification check
            $passwordCheck = Hash::check($credentials['password'], $user->password);
            Log::info('Password check result for user ' . $user->id . ': ' . ($passwordCheck ? 'Match' : 'Mismatch'));

            // Attempt JWT authentication
            if (!$token = JWTAuth::attempt($credentials)) {
                Log::warning('JWTAuth attempt failed for user: ' . $user->id);
                return response()->json([
                    'status' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            Log::info('Successful login for user: ' . $user->id);

            return response()->json([
                'status' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user' => $user
                ]
            ]);
        } catch (JWTException $e) {
            Log::error('JWT Exception during login: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Tidak dapat membuat token'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat login'
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = auth('api')->user();
            return response()->json([
                'status' => true,
                'data' => $user
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token tidak valid'
            ], 401);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'status' => true,
                'message' => 'Berhasil logout'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal logout'
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            return $this->respondWithToken($token);
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token tidak dapat diperbarui'
            ], 401);
        }
    }

    protected function respondWithToken($token)
    {
        $user = auth('api')->user();

        return response()->json([
            'status' => true,
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $user
            ]
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = auth('api')->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password saat ini tidak sesuai'
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            Log::info('Password changed successfully for user: ' . $user->id);

            return response()->json([
                'status' => true,
                'message' => 'Password berhasil diubah'
            ]);
        } catch (\Exception $e) {
            Log::error('Password change error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengubah password'
            ], 500);
        }
    }

    public function sendOtp(SendOtpRequest $request)
    {
        try {
            $email = $request->email;

            // Generate 4-digit OTP
            $otp = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

            // Store OTP in cache for 5 minutes
            $cacheKey = 'password_reset_otp_' . $email;
            \Cache::put($cacheKey, $otp, 300); // 5 minutes

            // TODO: Send OTP via email
            // For now, we'll just return the OTP in response (for testing)
            // In production, you should send this via email

            Log::info('OTP sent for password reset: ' . $email . ' - OTP: ' . $otp);

            Mail::to($email)->send(new OtpMail($otp));

            return response()->json([
                'status' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda',
                'data' => [
                    'otp' => $otp // Remove this in production
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Send OTP error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengirim kode OTP'
            ], 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $email = $request->email;
            $otp = $request->otp;

            // Verify OTP
            $cacheKey = 'password_reset_otp_' . $email;
            $storedOtp = \Cache::get($cacheKey);

            if (!$storedOtp || $storedOtp !== $otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kode OTP tidak valid atau sudah kadaluarsa'
                ], 400);
            }

            // Find user and update password
            $user = M_pengguna::where('email', $email)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Remove OTP from cache
            \Cache::forget($cacheKey);

            Log::info('Password reset successfully for user: ' . $user->id);

            return response()->json([
                'status' => true,
                'message' => 'Password berhasil direset'
            ]);
        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat reset password'
            ], 500);
        }
    }
}
