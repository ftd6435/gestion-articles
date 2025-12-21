<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    use ImageUpload;

    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $fields = $request->validated();
        $fields['password'] = Hash::make($fields['password']);

        // if ($request->hasFile('image')) {
        //     $fields['image'] = $this->imageUpload($request->file('image'), 'users');
        // }

        $user = User::create($fields);

        return view('auth.login')->with('message', "Compte créé avec succès.");
    }
}
