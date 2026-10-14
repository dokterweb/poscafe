<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
     // Menampilkan Form Login
     public function index()
     {
         return view('auth.login');
     }
 
     public function handleLogin(Request $request)
     {
         $credential = $request->validate([
             'email' => 'required|email|exists:users,email',
             'password' => 'required|string|min:8', // Set password minimum 8 karakter
         ],[
             'email.required'    => 'Email harus di isi',
             'email.email'       => 'Email tidak valid',
             'password.required' => 'Password harus di isi',
             'password.min'      => 'Password harus memiliki minimal 8 karakter',
         ]);
         
 
         if (Auth::attempt($credential)) {
             // dd('berhasil login');
             $request->session()->regenerate();
             $user = Auth::user();
             if ($user->hasRole('admin')) {
                 return redirect()->route('admin.dashboard');
             } else if ($user->hasRole('ustadz')) {
                return redirect()->route('ustadz.dashboard');
             } else {
                 return redirect()->route('santri.dashboard');
             }
         }
         return back()->withErrors([
             'email'     => 'Tidak sesuai dengan database',
         ])->onlyInput('email');
         
     }
 
     public function changePasswordForm()
     {
         $user = auth()->user();
         return view('auth.change-password', compact('user'));
     }
 
     public function updatePassword(Request $request)
     {
         $user = auth()->user();
         $request->validate([
            'name'              => 'nullable|min:6',
             'email'            => 'nullable|email|unique:users,email,' . $user->id,
             'current_password'  => 'nullable|required_with:new_password',
             'new_password'      => 'nullable|min:6|confirmed',
             'avatar'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
         ]);
         
           /** ======================
          *  UPDATE EMAIL (opsional)
          *  ====================== */
         if ($request->filled('email')) {
             $user->email = $request->email;
         }
         
         /** ======================
          *  GANTI PASSWORD (opsional)
          *  ====================== */
         if ($request->filled('new_password')) {
     
             // cek password lama
             if (! Hash::check($request->current_password, $user->password)) {
                 return back()->withErrors([
                     'current_password' => 'Password lama salah!',
                 ])->withInput();
             }
     
             // update password
             $user->password = Hash::make($request->new_password);
         }
     
         /** ======================
          *  GANTI AVATAR (opsional)
          *  ====================== */
         if ($request->hasFile('avatar')) {
             $file = $request->file('avatar');
         
             $filename = time().'_'.$file->getClientOriginalName();
         
             // path menuju public_html/images/avatars
             $uploadPath = $_SERVER['DOCUMENT_ROOT'].'/images/avatars/';
         
             // pastikan folder ada
             if (!file_exists($uploadPath)) {
                 mkdir($uploadPath, 0775, true);
             }
         
             // pindahkan file
             $file->move($uploadPath, $filename);
         
             // hapus foto lama
             if ($user->avatar && $user->avatar !== 'images/default-avatar.png') {
                 $oldPath = $_SERVER['DOCUMENT_ROOT'].'/'.$user->avatar;
                 if (file_exists($oldPath)) {
                     @unlink($oldPath);
                 }
             }
         
             // simpan path baru
             $user->avatar = 'images/avatars/'.$filename;
         }
     
         $user->save();
     
         return redirect()->route('dashboard')->with('success', 'Data akun berhasil diperbarui!');
     }

     
     public function logout(Request $request)
     {
         Auth::logout();
         $request->session()->invalidate();
         $request->session()->regenerateToken();
         return redirect('/');
     }
}
