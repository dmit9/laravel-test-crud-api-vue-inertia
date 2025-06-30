<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Task;

class TelegramController extends Controller
{
    public function telegram(Request $request)
    {
      $tasks = Task::all();

    //   dd($tasks);
        
      return Inertia::render('Frontend/Telegram', ['tasks' => $tasks]);
    }
}
