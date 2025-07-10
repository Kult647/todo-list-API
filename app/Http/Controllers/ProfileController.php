<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $profile = $request->user()->profile;
        return new \App\Http\Resources\ProfileResource($profile);
    }

    public function update(UpdateProfileRequest $request)
    {
        $profile = $request->user()->profile;
        $profile->update($request->validated());

        return new \App\Http\Resources\ProfileResource($profile);
    }
}
