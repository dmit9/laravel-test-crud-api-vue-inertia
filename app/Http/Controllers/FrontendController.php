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

        $sortField = $request->query('sort', 'id');
        $sortDirection = $request->query('direction', 'desc');
        $searchField = $request->query('search', '');

        // Валидация
        $validSortFields = ['id', 'name', 'created_at'];
        $sortField = in_array($sortField, $validSortFields) ? $sortField : 'id';
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $positions = Position::all();
        $searchField = preg_replace("#([%_?+])#", "\\$1", $searchField);

        $users = User::with('position')
            ->where(function ($query) use ($searchField) {
                $query->where('name', 'LIKE', "%{$searchField}%")
                    ->orWhere('email', 'LIKE', "%{$searchField}%")
                    ->orWhere('phone', 'LIKE', "%{$searchField}%");
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(6);

        return Inertia::render('Frontend/Home', [
            'users' => $users,
            'positions' => $positions,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
            'searchField' => $searchField,
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
       // dd($user);
        $user->delete();
       // return redirect()->route('home');

        //return redirect()->to('/');
        return Inertia::location(route('home'));
      //  return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
