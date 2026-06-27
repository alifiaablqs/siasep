<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class AuthController extends BaseApiController
{
    /**
     * Handle API login
     */
    public function login(Request $request)
    {
        $request->validate([
            'credential' => 'required|string',
            'password'   => 'required|string',
        ]);

        $user = User::where('email', $request->credential)
                    ->orWhere('nip', $request->credential)
                    ->first();

        if (!$user) {
            return $this->error('Akun tidak terdaftar.', 401);
        }

        // Cek password lokal
        $authenticated = Hash::check($request->password, $user->password);

        if (!$authenticated) {
            // Cek ke API eksternal
            try {
                $apiUrl = rtrim(config('services.external_api.base_url'), '/');
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                ])->post("{$apiUrl}/login", [
                    'login' => $user->nip,
                    'password' => $request->password,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['status']) && $data['status'] === 'success') {
                        // Password benar di sistem eksternal, update lokal.
                        $user->update([
                            'password' => Hash::make($request->password)
                        ]);
                        $authenticated = true;
                    }
                }
            } catch (\Exception $e) {
                Log::error("API Pass-through Auth Error untuk NIP {$user->nip}: " . $e->getMessage());
            }
        }

        if (!$authenticated) {
            return $this->error('Password yang Anda masukkan salah.', 401);
        }

        if (!$user->role) {
            return $this->error('Akun ini tidak memiliki role.', 403);
        }

        // Generate secure encrypted Bearer Token with Sanctum
        $token = $user->createToken('API-Token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user'  => [
                'id'         => $user->id,
                'firstname'  => $user->firstname,
                'lastname'   => $user->lastname,
                'fullname'   => $user->fullname,
                'nip'        => $user->nip,
                'email'      => $user->email,
                'role'       => $user->role ? $user->role->nm_role : null,
                'kode_bagian'=> $user->kode_bagian,
            ]
        ], 'Login berhasil.');
    }

    /**
     * Handle API logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout berhasil. Token telah dicabut dari server.');
    }

    /**
     * Get current authenticated user details
     */
    public function me()
    {
        $user = Auth::user();
        return $this->success([
            'id'         => $user->id,
            'firstname'  => $user->firstname,
            'lastname'   => $user->lastname,
            'fullname'   => $user->fullname,
            'nip'        => $user->nip,
            'email'      => $user->email,
            'role'       => $user->role ? $user->role->nm_role : null,
            'kode_bagian'=> $user->kode_bagian,
        ], 'Profile retrieved successfully.');
    }
}
