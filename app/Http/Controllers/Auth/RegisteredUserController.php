<?php

namespace App\Http\Controllers\Auth;

use Tinify\Source;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Position;
use function Tinify\setKey;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Service\IndexService;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Intervention\Image\Facades\Image;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\StoreUserRequest;

class RegisteredUserController extends Controller
{
    public IndexService $IndexService;

    public function __construct(IndexService $IndexService)
    {
        $this->IndexService = $IndexService;
    }
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $positions = Position::all();
        return Inertia::render('Auth/Register', compact('positions'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $this->IndexService->imagesCompress( $request);

        $data['password'] = Hash::make($request->password);

        $user = User::create($data);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home', absolute: false));
    }
}
