<?php

namespace App\Http\Controllers\Auth;

use Tinify\Source;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Position;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Intervention\Image\Facades\Image;
use Illuminate\Auth\Events\Registered;
use function Tinify\setKey;

class RegisteredUserController extends Controller
{
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'regex:/[0-9]*$/','min:2','max:20'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'photo' => ['required', 'image', 'mimes:jpeg', 'dimensions:min_width=70,min_height=70', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = Str::random(15) . '.jpg';

            $image = Image::make($photo->getPathname())
                ->fit(70, 70, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', 90);

            $tempPath = storage_path('app/public/images/temp/' . $filename);
            $image->save($tempPath);

            setKey('1BC4cXMKstgLxcKJbWV6qnkfkzTzJ1VK');
            $source = Source::fromFile($tempPath);
            $optimizedPath = 'images/users/' . $filename;

            $finalStoragePath = storage_path('app/public/' . $optimizedPath);
            $source->toFile($finalStoragePath);
            unlink($tempPath);

            $publicStoragePath = public_path('storage/' . $optimizedPath);
            $publicDir = dirname($publicStoragePath);
            if (!file_exists($publicDir)) {
                mkdir($publicDir, 0755, true);
            }
            copy($finalStoragePath, $publicStoragePath);
        }
        $data = [
            'photo' => $optimizedPath,
            'phone' => $request->phone,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'position_id' => $request->position_id,
        ];

        $user = User::create($data);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home', absolute: false));
    }
}
