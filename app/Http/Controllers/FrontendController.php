<?php

namespace App\Http\Controllers;

use Tinify\Source;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Position;
use function Tinify\setKey;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use App\Http\Requests\UpdateUserRequest;

class FrontendController extends Controller
{
    public function index(Request $request)
    {

        $sortField = $request->query('sort', 'id'); // По умолчанию сортируем по 'id'
        $sortDirection = $request->query('direction', 'desc'); // По умолчанию 'desc' (от новых к старым)
        $positions = Position::all();
        $users = User::with('position')->orderBy($sortField, $sortDirection)->paginate(6);

        return Inertia::render('Frontend/Home', [
            'users' => $users,
            'positions' => $positions,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
        ]);
    }

    public function show(User $user)
    {
        return Inertia::render('Frontend/Show', [
            'user' => $user,
        ]);
    }

    public function edit(User $user)
    {
        $positions = Position::all();
        return Inertia::render('Frontend/Edit', [
            'user' => $user,
            'positions' => $positions,
        ]);
    }

    public function update(Request $request, User $user)
    {
        // dd($request);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user), 
            ],
            'phone' => ['required', 'regex:/[0-9]*$/', 'min:2', 'max:20'],
            'position_id' => ['required', 'integer', 'exists:positions,id'],
            'photo' => [
                'nullable',
                'sometimes', // Если фото вообще передаётся
                'file', // Проверяем, что это файл
                'image', // Проверяем, что это картинка
                'dimensions:min_width=70,min_height=70',
                'max:5120'
            ],
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
            $data = [
                'photo' => $optimizedPath,
                'phone' => $request->phone,
                'name' => $request->name,
                'email' => $request->email,
                'position_id' => $request->position_id,
            ];
        } else {
             $data = [
                 'photo' => $request->photo,
                 'phone' => $request->phone,
                 'name' => $request->name,
                 'email' => $request->email,
                 'position_id' => $request->position_id,
             ];
        }
        

       // $user->update(['name' => $request->name]);
         $user->update((array)$data);

        return Inertia::render('Frontend/Show', [
            'user' => $user
        ]);
    }

    public function delete(User $user)
    {
        return Inertia::render('Frontend/Edit', [
            'user' => $user,

        ]);
    }
}
