<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index(Request $request)
    {
 //       Log::info("TaskController: index method called");

        $query = Task::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'ilike', "%{$request->search}%")
                    ->orWhere('description', 'ilike', "%{$request->search}%");
            });
        }

        return response()->json($query->with('files')->get());
    }

    /**
     * Получить задачи пользователя (для Telegram бота)
     */
    public function getUserTasks(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $isAdmin = $request->input('is_admin', false);

            if (!$userId) {
                return response()->json(['message' => 'User ID не указан'], 400);
            }

            $query = $isAdmin ? Task::query() : Task::where('user_id', $userId);
            $tasks = $query->orderBy('created_at', 'desc')->get();

            return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error("Ошибка при получении задач пользователя", [
                'error' => $e->getMessage(),
                'user_id' => $request->input('user_id')
            ]);
            return response()->json(['message' => 'Ошибка при получении задач'], 500);
        }
    }

    /**
     * Удалить задачу (для Telegram бота)
     */
    public function deleteTask(Request $request)
    {
        try {
            $taskId = $request->input('task_id');
            $userId = $request->input('user_id');
            $isAdmin = $request->input('is_admin', false);

            if (!$taskId) {
                return response()->json(['message' => 'ID задачи не указан'], 400);
            }

            $query = Task::where('id', $taskId);

            // Если не админ, проверяем принадлежность задачи пользователю
            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            $task = $query->first();

            if (!$task) {
                return response()->json([
                    'message' => 'Задача не найдена или у вас нет прав на её удаление'
                ], 404);
            }

            $task->delete();

            Log::info("Задача удалена", [
                'task_id' => $taskId,
                'user_id' => $userId,
                'is_admin' => $isAdmin
            ]);

            return response()->json(['message' => 'Задача успешно удалена']);
        } catch (\Exception $e) {
            Log::error("Ошибка при удалении задачи", [
                'error' => $e->getMessage(),
                'task_id' => $request->input('task_id'),
                'user_id' => $request->input('user_id')
            ]);
            return response()->json(['message' => 'Ошибка при удалении задачи'], 500);
        }
    }

    /**
     * Создать задачу (для Telegram бота)
     */
    public function createTask(Request $request)
    {
        try {
            $telegramId = $request->input('telegram_id');
            $title = $request->input('title');
            $description = $request->input('description');
            $status = $request->input('status', 'pending');

            if (!$title || !$description) {
                return response()->json(['message' => 'Обязательные поля не заполнены'], 400);
            }

            // Валидация статуса
            if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
                $status = 'pending';
            }
            $userId = TelegramUser::where('telegram_id', $telegramId)->value('id');

            $task = Task::create([
                'user_id' => $userId,
                'title' => $title,
                'description' => $description,
                'status' => $status,
            ]);

            Log::info("Задача создана", [
                'task_id' => $task->id,
                'user_id' => $userId,
                'title' => $title
            ]);

            return response()->json($task, 201);
        } catch (\Exception $e) {
            Log::error("Ошибка при создании задачи", [
                'error' => $e->getMessage(),
                'user_id' => $request->input('user_id')
            ]);
            return response()->json(['message' => 'Ошибка при создании задачи'], 500);
        }
    }

    /**
     * Обновить задачу (для Telegram бота)
     */
    public function updateTask(Request $request)
    {
        try {
            $taskId = $request->input('task_id');
            $userId = $request->input('user_id');
            $isAdmin = $request->input('is_admin', false);
            $oldStatus = null;

            if (!$taskId) {
                return response()->json(['message' => 'ID задачи не указан'], 400);
            }

            $query = Task::where('id', $taskId);

            // Если не админ, проверяем принадлежность задачи пользователю
            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }

            $task = $query->first();

            if (!$task) {
                return response()->json([
                    'message' => 'Задача не найдена или у вас нет прав на её изменение'
                ], 404);
            }

            $oldStatus = $task->status;
            $updateData = [];

            if ($request->has('title')) {
                $updateData['title'] = $request->input('title');
            }

            if ($request->has('description')) {
                $updateData['description'] = $request->input('description');
            }

            if ($request->has('status')) {
                $status = $request->input('status');
                if (in_array($status, ['pending', 'in_progress', 'completed'])) {
                    $updateData['status'] = $status;
                }
            }

            if (empty($updateData)) {
                return response()->json(['message' => 'Нет данных для обновления'], 400);
            }

            $task->update($updateData);

            Log::info("Задача обновлена", [
                'task_id' => $taskId,
                'user_id' => $userId,
                'updated_fields' => array_keys($updateData),
                'status_changed' => isset($updateData['status']) ? 'yes' : 'no'
            ]);

            return response()->json($task);
        } catch (\Exception $e) {
            Log::error("Ошибка при обновлении задачи", [
                'error' => $e->getMessage(),
                'task_id' => $request->input('task_id'),
                'user_id' => $request->input('user_id')
            ]);
            return response()->json(['message' => 'Ошибка при обновлении задачи'], 500);
        }
    }

    public function getFilteredTasks(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $isAdmin = $request->input('is_admin', false);
            $status = $request->input('status');
            $search = $request->input('search');
            Log::info("TaskController: getFilteredTasks method called", [
                'user_id' => $userId, 'is_admin' => $isAdmin,'status' => $status,'search' => $search
            ]);

            if (!$userId) {
                return response()->json(['message' => 'User ID не указан'], 400);
            }

            $query = $isAdmin ? Task::query() : Task::where('user_id', $userId);

            // Фильтр по статусу
            if ($status) {
                $query->where('status', $status);
                Log::info(" фильтр применен  'status' => $status"  );
            }

            // Поиск по тексту
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            $tasks = $query->with(['files' => function ($query) {
                $query->select('id', 'task_id', 'file_name', 'file_type', 'created_at');
            }])->orderBy('created_at', 'desc')->get();

            return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error("Ошибка при получении отфильтрованных задач", [
                'error' => $e->getMessage(),
                'user_id' => $request->input('user_id')
            ]);
            return response()->json(['message' => 'Ошибка при получении задач'], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed',
        ]);

        $task = Task::create([
            'user_id' => auth()->id(), // Assumes API authentication
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'pending',
        ]);

        return response()->json($task, 201);
    }

    public function show($id)
    {
        $task = Task::with('files')->findOrFail($id);
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed',
        ]);

        $task->update($request->only(['title', 'description', 'status']));
        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        return response()->json(null, 204);
    }

}
