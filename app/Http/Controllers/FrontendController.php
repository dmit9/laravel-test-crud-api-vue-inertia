<?php

namespace App\Http\Controllers;

use Tinify\Source;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Position;
use function Tinify\setKey;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Service\IndexService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use App\Http\Requests\UpdateUserRequest;

class FrontendController extends Controller
{
    public IndexService $IndexService;

    public function __construct(IndexService $IndexService)
    {
        $this->IndexService = $IndexService;
    }
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

    public function update(UpdateUserRequest $request, User $user)
    {

        $data = $this->IndexService->imagesCompress($request);

        $user->update((array)$data);

        return Inertia::render('Frontend/Show', [
            'user' => $user
        ]);
    }

    public function delete(User $user)
    {
   //   dd($user);
    //    $user->delete();

        //return redirect()->to('/');
      //  return Inertia::location(route('home'));
      //  return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
