<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Tinify\Source;
use function Tinify\setKey;

class UserController extends Controller
{
    public function index()
    {
        return UserResource::collection(User::paginate(6));
    }

  
    public function show(User $user)
    {
        return new UserResource($user);
    }

}
