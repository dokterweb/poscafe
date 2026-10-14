<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
     public function index()
    {
        $user = auth()->user();

        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'avatar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'name.required' => 'Nama wajib diisi.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah digunakan user lain.',

            'avatar.image' => 'File avatar harus berupa gambar.',
            'avatar.mimes' => 'Avatar harus berformat JPG, JPEG, PNG, atau WEBP.',
            'avatar.max' => 'Ukuran avatar maksimal 2 MB.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Simpan Avatar Baru
        |--------------------------------------------------------------------------
        */

        $oldAvatar = $user->avatar;

        if ($request->hasFile('avatar')) {

            $avatarPath = $request->file('avatar')->store(
                'avatars',
                'public'
            );

            $validated['avatar'] = $avatarPath;
        }

        /*
        |--------------------------------------------------------------------------
        | Update Data User
        |--------------------------------------------------------------------------
        */

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (isset($validated['avatar'])) {
            $user->avatar = $validated['avatar'];
        }

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Hapus Avatar Lama
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['avatar']) &&
            $oldAvatar &&
            Storage::disk('public')->exists($oldAvatar)
        ) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui.',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar
                    ? asset('storage/' . $user->avatar)
                    : null,
            ],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'current_password.required' =>
                'Password saat ini wajib diisi.',

            'current_password.current_password' =>
                'Password saat ini tidak benar.',

            'password.required' =>
                'Password baru wajib diisi.',

            'password.min' =>
                'Password baru minimal 8 karakter.',

            'password.confirmed' =>
                'Konfirmasi password tidak cocok.',
        ]);

        $user->password = Hash::make($validated['password']);

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui.',
        ]);
    }

}
