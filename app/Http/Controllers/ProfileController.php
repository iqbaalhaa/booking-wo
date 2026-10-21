<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $request->user()->update($request->validated());

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function password(UpdatePasswordRequest $request)
    {
        $request->user()->update(['password' => $request->validated('password')]); // di-hash oleh cast 'hashed'

        return redirect()->route('profile.edit')->with('success', 'Kata sandi berhasil diganti.');
    }
}
