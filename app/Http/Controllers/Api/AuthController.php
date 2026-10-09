<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * ================================================================
     * TENANT (TOKO) SAAT INI
     * ================================================================
     *
     * Diisi oleh middleware ResolveTenant dari subdomain / header X-Tenant.
     * null = situs utama.
     */
    private function currentTenant(Request $request): ?User
    {
        $tenant = $request->attributes->get('tenant');

        return $tenant instanceof User ? $tenant : null;
    }

    private function tenantPayload(?User $tenant): ?array
    {
        if (! $tenant) {
            return null;
        }

        return [
            'name' => $tenant->name,
            'subdomain' => $tenant->subdomain,
        ];
    }

    /**
     * ================================================================
     * INFO TOKO
     * ================================================================
     *
     * GET /api/tenant
     *
     * Dipakai aplikasi untuk memastikan kode toko valid dan menampilkan
     * nama tokonya. Kalau kode tidak valid, middleware sudah membalas 404.
     */
    public function tenant(Request $request)
    {
        return response()->json([
            'tenant' => $this->tenantPayload(
                $this->currentTenant($request)
            ),
        ]);
    }

    /**
     * ================================================================
     * REGISTER USER
     * ================================================================
     *
     * User langsung active.
     *
     * User TIDAK dibuatkan wallet.
     *
     * Karena user membeli produk menggunakan Midtrans
     * per transaksi.
     *
     * Kalau mendaftar lewat subdomain toko, user otomatis menjadi
     * user di bawah agen pemilik toko (parent_agent_id).
     */
    public function register(Request $request)
    {
        $tenant = $this->currentTenant($request);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
        ]);

        $user = User::create([
            'name' => $data['name'],

            'email' => $data['email'],

            'password' => Hash::make(
                $data['password']
            ),

            'phone' => $data['phone'] ?? null,

            'role' => 'user',

            'status' => 'active',

            'parent_agent_id' => $tenant?->id,
        ]);

        return response()->json([
            'message' =>
            'Registrasi berhasil.',

            'user' => $user,

            'tenant' => $this->tenantPayload($tenant),

            'token' =>
            $user
                ->createToken('api')
                ->plainTextToken,
        ], 201);
    }

    /**
     * ================================================================
     * REGISTER AGEN
     * ================================================================
     *
     * Agen tidak langsung aktif.
     *
     * Flow:
     *
     * register
     *     ↓
     * pending
     *     ↓
     * superadmin approve
     *     ↓
     * active
     *     ↓
     * wallet dibuat
     *
     * Agen mendapat subdomain toko sendiri. Boleh diisi saat daftar
     * (field "subdomain"); kalau kosong dibuat otomatis dari nama.
     * Subdomain baru bisa dipakai setelah agen disetujui (active).
     *
     * Pendaftaran agen hanya lewat situs utama, bukan lewat toko agen lain.
     */
    public function registerAgen(
        Request $request
    ) {
        if ($this->currentTenant($request)) {
            return response()->json([
                'message' =>
                'Pendaftaran agen hanya bisa dilakukan '
                    . 'melalui situs utama.',
            ], 403);
        }

        if ($request->filled('subdomain')) {
            $request->merge([
                'subdomain' => strtolower(
                    trim((string) $request->input('subdomain'))
                ),
            ]);
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],

            'subdomain' => [
                'nullable',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])$/',
                Rule::notIn(config('tenancy.reserved', [])),
                'unique:users,subdomain',
            ],
        ], [
            'subdomain.regex' =>
            'Subdomain hanya boleh huruf kecil, angka, dan tanda minus.',

            'subdomain.not_in' =>
            'Subdomain ini tidak boleh dipakai.',

            'subdomain.unique' =>
            'Subdomain sudah dipakai agen lain.',
        ]);

        $user = User::create([
            'name' => $data['name'],

            'email' => $data['email'],

            'password' => Hash::make(
                $data['password']
            ),

            'phone' => $data['phone'],

            'role' => 'agen',

            'status' => 'pending',

            'subdomain' => $data['subdomain']
                ?? User::generateSubdomain($data['name']),
        ]);

        /*
         * JANGAN buat wallet di sini.
         *
         * Wallet dibuat ketika superadmin approve.
         */

        return response()->json([
            'message' =>
            'Pendaftaran agen berhasil. '
                . 'Akun menunggu persetujuan superadmin. '
                . 'Alamat toko Anda nanti: '
                . $user->subdomain . '.'
                . config('tenancy.main_domain'),

            'user' => $user,
        ], 201);
    }

    /**
     * ================================================================
     * LOGIN
     * ================================================================
     *
     * Satu endpoint untuk:
     *
     * USER
     * AGEN
     * SUPERADMIN
     *
     * Situs utama (tanpa subdomain): semua akun boleh login.
     *
     * Lewat subdomain toko: hanya agen pemilik toko dan user di
     * bawahnya yang boleh login. Akun lain mendapat pesan yang sama
     * dengan password salah, supaya keberadaan akun tidak bocor.
     */
    public function login(
        Request $request
    ) {
        $tenant = $this->currentTenant($request);

        $data = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $user = User::where(
            'email',
            $data['email']
        )->first();

        /*
         * Credential salah, atau akun bukan milik toko ini.
         */
        if (
            ! $user ||
            ! Hash::check(
                $data['password'],
                $user->password
            ) ||
            ($tenant && ! $user->belongsToTenant($tenant))
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'Email atau password salah.',
                ],
            ]);
        }

        /*
         * ============================================================
         * PENDING
         * ============================================================
         */

        if ($user->isPending()) {
            throw ValidationException::withMessages([
                'email' => [
                    'Akun Anda masih menunggu '
                        . 'persetujuan superadmin.',
                ],
            ]);
        }

        /*
         * ============================================================
         * SUSPENDED
         * ============================================================
         */

        if ($user->isSuspended()) {
            throw ValidationException::withMessages([
                'email' => [
                    'Akun Anda sedang dinonaktifkan. '
                        . 'Silakan hubungi superadmin.',
                ],
            ]);
        }

        /*
         * ============================================================
         * ACTIVE
         * ============================================================
         */

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                'email' => [
                    'Status akun tidak valid.',
                ],
            ]);
        }

        /*
         * Token dibuat setelah status lolos.
         */
        $token = $user
            ->createToken('api')
            ->plainTextToken;

        /*
         * Load wallet hanya jika ada.
         *
         * User dan superadmin tidak mempunyai wallet.
         */
        $user->load('wallet');

        return response()->json([
            'message' =>
            'Login berhasil.',

            'user' => $user,

            'tenant' => $this->tenantPayload($tenant),

            'token' => $token,
        ]);
    }

    /**
     * ================================================================
     * LOGOUT
     * ================================================================
     */
    public function logout(
        Request $request
    ) {
        $request
            ->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'message' =>
            'Logout berhasil.',
        ]);
    }

    /**
     * ================================================================
     * ME
     * ================================================================
     */
    public function me(
        Request $request
    ) {
        return response()->json(
            $request
                ->user()
                ->load('wallet')
        );
    }

    /**
     * ================================================================
     * UPDATE PROFILE
     * ================================================================
     */
    public function updateProfile(
        Request $request
    ) {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
        ]);

        $user = $request->user();

        $user->update($data);

        return response()->json([
            'message' =>
            'Profil berhasil diperbarui.',

            'user' =>
            $user
                ->fresh()
                ->load('wallet'),
        ]);
    }

    /**
     * ================================================================
     * CHANGE PASSWORD
     * ================================================================
     */
    public function changePassword(
        Request $request
    ) {
        $data = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user = $request->user();

        if (
            ! Hash::check(
                $data['current_password'],
                $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'Password saat ini salah.',
                ],
            ]);
        }

        $user->update([
            'password' => Hash::make(
                $data['password']
            ),
        ]);

        /*
         * Semua token lama dicabut.
         */
        $user->tokens()->delete();

        return response()->json([
            'message' =>
            'Password berhasil diubah. '
                . 'Silakan login kembali.',
        ]);
    }
}
