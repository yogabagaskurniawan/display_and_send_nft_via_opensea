<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public function connectWallet()
    {
        // Auth::loginUsingId(1); // Login menggunakan ID user
        // return redirect()->intended('/token');
        $this->dispatch('connectWallet');
    }

    #[On('processWalletLogin')]
    public function processWalletLogin($addressData)
    {
        // dd($addressData);
        try {
            // Ambil alamat wallet dari parameter
            $walletAddress = $addressData;

            DB::beginTransaction();

            // Cek apakah user dengan wallet address sudah terdaftar
            $currentUser = User::where('email', $walletAddress)->first();

            if (!$currentUser) {
                // Buat user baru jika belum ada
                $uid = mt_rand(1, 999999999); // Generate UID secara acak

                $currentUser = new User(); // Buat instance User baru
                $currentUser->email = $walletAddress; // Set email user
                $currentUser->uid = $uid; // Set UID user
                $currentUser->password = Hash::make($walletAddress); // password disamakan dengan wallet
                $currentUser->wallet_address = $walletAddress; // Set alamat wallet
                $currentUser->save(); // Simpan user baru

                $userId = $currentUser->id; // Ambil ID user yang baru dibuat
            } else {
                $currentUser->wallet_address = $walletAddress; // Simpan wallet address
                $currentUser->save(); // Simpan perubahan
                $userId = $currentUser->id; // Ambil ID user yang sudah ada
            }

            DB::commit();
            // Login user ke sistem
            Auth::loginUsingId($userId); // Login menggunakan ID user
            return redirect('/wallet/history'); // Redirect ke dashboard

        } catch (\Throwable $th) {

            DB::rollBack();
            // addSystemLog('wallet-login', $th, Auth::id() ?? 0);
            $this->dispatch('failed-message', 'Wallet authentication failed. Please try again.'); // Kirim notifikasi gagal
            return;
        }
    }
};