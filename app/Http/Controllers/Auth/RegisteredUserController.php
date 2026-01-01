<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        logActivity('created a user', [
            'added'   => [
                'name' => $user->name,
                'telephone' => $user->telephone
            ],
            'author' => [
                'name' => Auth::user()->name,
                'telephone' => Auth::user()->telephone,
            ]
        ], $user);

        return view('livewire.dashboard')->with('message', "Compte créé avec succès.");
    }
}
