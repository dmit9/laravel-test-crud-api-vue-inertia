<?php

namespace App\Telegram;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use App\Models\TelegramUser;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Validator;

class Handler extends WebhookHandler
{
    protected $user;
    protected $taskController;

    public function __construct()
    {
        $this->taskController = new TaskController();
    }

    protected function handleUnknownCommand(Stringable $text): void
    {
        $this->chat->html("There is no such command: $text")->send();
        $this->reply("Available commands:\n" .
            "/help - all teams\n" .
            "/start - welcome and registration\n" .
            "/list_tasks - show tasks\n" .
            "/create_task - create a task\n" .
            "/delete_task - delete task\n" .
            "/update_task - update task\n" .
            "/filter_tasks - filter tasks");
    }
    public function start()
    {
        // Авторизация уже пройдена в handleTextCommand
        if (!$this->user) {
            $this->user = $this->authorizeUser();
            if (!$this->user) return;
        }
        $firstName = $this->message->from()->firstName(); // Имя

        $this->reply("👋 Hello, $firstName. I am a task management bot." );
        Log::info(" $firstName registered in the system.");
    }

    public function help()
    {
        $this->reply("Available commands:\n" .
            "/help - all teams\n" .
            "/start - welcome and registration\n" .
            "/list_tasks - show tasks\n" .
            "/create_task - create a task\n" .
            "/delete_task - delete task\n" .
            "/update_task - update task\n" .
            "/filter_tasks - filter tasks");

            $firstName = $this->message->from()->firstName();
            Log::info("++++++++++++++ $firstName help.");
    }
    private function authorizeUser()
    {
        // Проверяем наличие сообщения или callback-запроса
        if (!$this->message && !$this->callbackQuery) {
            Log::error('Ни сообщение, ни callback-запрос не получены в authorizeUser');
            if ($this->chat) {
                $this->chat->html("Error: Data not received.")->send();
            }
            return null;
        }

        // Получаем telegram_id из сообщения или callback-запроса
        $telegramId = $this->message
            ? $this->message->from()->id()
            : $this->callbackQuery->from()->id();

        try {
            $user = TelegramUser::where('telegram_id', $telegramId)->first();

            if (!$user) {
                $user = TelegramUser::create([
                'telegram_id' => $telegramId,
                'username' => $this->message->from()->username(),
                'first_name' => $this->message->from()->firstName(),
                'last_name' => $this->message->from()->lastName(),
                'is_admin' => false,
            ]);
            }

            return $user;
        } catch (\Exception $e) {
            Log::error("Ошибка при авторизации пользователя", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if ($this->chat) {
                $this->chat->html("An error occurred while authorizing. Try again.!")->send();
            }
            return null;
        }
    }

    private function getUserProperty($property)
    {
        if ($this->message) {
            return $this->message->from()->$property() ?? null;
        }
        if ($this->callbackQuery) {
            return $this->callbackQuery->from()->$property() ?? null;
        }
        return null;
    }

    protected function askTitle()
    {
        // Clear any previous temporary data
        $this->chat->storage()->forget('temp_title');
        $this->chat->storage()->forget('temp_description');

        $this->reply("Specify the task title.");
        $this->chat->storage()->set('next_step', 'ask_title');
    }

    protected function askDescription()
    {
        $this->reply("Please provide a description of the task.");
        $this->chat->storage()->set('next_step', 'ask_description');
    }

    public function handleChatMessage(Stringable $text): void
    {
        Log::info("handleChatMessage вызван ");
        $textValue = $text->toString();
        $nextStep = $this->chat->storage()->get('next_step');

        switch ($nextStep) {
            case 'ask_title':
                Log::info(" handleChatMessage ask_title");
                $this->processTitle($textValue);
                break;
            case 'ask_description':
                Log::info(" handleChatMessage ask_description");
                $this->processDescription($textValue);
                break;
            case 'text_search':
                Log::info(" handleChatMessage text_search");
                $this->processTextSearch($textValue);
                break;
            default:
                parent::handleChatMessage($text);
        }
    }
    public function processTextSearch($searchText)
    {
        Log::info("function processTextSearch");
        
        $this->user = $this->authorizeUser();
        
        if (empty(trim($searchText))) {
            $this->reply("Search query cannot be empty. Try again!");
            return;
        }
        // $telegramId = $this->message   ? $this->message->from()->id()

        try {
            $request = new Request([
                'user_id' => $this->user->id,
                'is_admin' => $this->user->is_admin,
                'search' => $searchText
            ]);
            Log::info("function processTextSearch request: " . json_encode($request->all()));

            $response = $this->taskController->getFilteredTasks($request);
            $tasks = $response->getData();

            if (empty($tasks)) {
                $this->reply("🔍 Problems with text '$searchText' not found. Try another text.");
                $this->filter_tasks();
            } else {
                $this->displayFilteredTasks($tasks, "Search results for the query: '$searchText'");
            }

            $this->chat->storage()->forget('next_step');
        } catch (\Exception $e) {
            Log::error("Ошибка при текстовом поиске", ['error' => $e->getMessage()]);
            $this->reply("There was an error while searching. Please try again.!");
        }
    }
    private function displayFilteredTasks($tasks, $title)
    {
        Log::info("function displayFilteredTasks title $title");
        $response = "📋 $title:\n\n";
        foreach ($tasks as $task) {
            $statusEmoji = $this->getStatusEmoji($task->status);
            $response .= "🆔 ID: {$task->id}\n";
            $response .= "📝 Headline: {$task->title}\n";
            $response .= "📊 Status: {$statusEmoji} {$task->status}\n";
            if ($task->description) {
                $response .= "📄 Description: " . mb_substr($task->description, 0, 100) . "\n";
            }

            // Показываем прикрепленные файлы если есть
            if (isset($task->files) && !empty($task->files)) {
                $response .= "📎 Files: " . count($task->files) . " шт.\n";
            }

            $response .= "─────────────────\n";
        }

        $this->chat->html($response)->send();
     //   $this->reply($response);
    }

    protected function processTitle(string $title)
{
    if (empty(trim($title))) {
        $this->reply("Task title cannot be empty. Please try again.!");
        return;
    }
    
    $validator = Validator::make(
        ['title' => $title],
        ['title' => 'required|string|max:100|min:2|regex:/^[\p{L}\p{N}\s\-\']+$/u']
    );

    if ($validator->fails()) {
        $this->reply("Error: " . $validator->errors()->first('title') . " try again!");
        return;
    }

    $taskId = $this->chat->storage()->get('update_task_id');
    if ($taskId) {
        // Обновляем только заголовок
        $this->updateSingleField($taskId, 'title', $title);
    } else {
        $this->chat->storage()->set('temp_title', $title);
        $this->askDescription();
    }
}

protected function processDescription(string $description)
{
    if (empty(trim($description))) {
        $this->reply("Task description cannot be empty. Try again!");
        return;
    }
    
    $validator = Validator::make(
        ['description' => $description],
        ['description' => 'required|string|max:100|min:2|regex:/^[\p{L}\p{N}\s\-\']+$/u']
    );

    if ($validator->fails()) {
        $this->reply("Error: " . $validator->errors()->first('description') . " try again!");
        return;
    }

    $taskId = $this->chat->storage()->get('update_task_id');
    if ($taskId) {
        // Обновляем только описание
        $this->updateSingleField($taskId, 'description', $description);
    } else {
        $this->chat->storage()->set('temp_description', $description);
        $this->createTaskFromStorage();
    }
}

protected function updateSingleField($taskId, $field, $value)
{
    if (!$this->user) {
        $this->user = $this->authorizeUser();
        if (!$this->user) return;
    }
    try {
        $requestData = [
            'task_id' => $taskId,
            'user_id' => $this->user->id,
            $field => $value
        ];

        $request = new Request($requestData);
        $result = $this->taskController->updateTask($request);

        if ($result->getStatusCode() === 200) {
            $this->chat->html("✅ Field '{$field}' tasks ID {$taskId} successfully updated!")->send();
        } else {
            $data = $result->getData();
            $this->chat->html("❌ Error: " . ($data->message ?? "Failed to update task"))->send();
        }

        // Очищаем storage
        $this->chat->storage()->forget('update_task_id');
        $this->chat->storage()->forget('update_field');
        $this->chat->storage()->forget('next_step');

    } catch (\Exception $e) {
        Log::error("Ошибка при обновлении поля задачи", [
            'error' => $e->getMessage(),
            'task_id' => $taskId,
            'field' => $field
        ]);
        $this->reply("An error occurred while updating the task. Please try again.!");
    }
}

    protected function createTaskFromStorage()
    {
        $title = $this->chat->storage()->get('temp_title');
        $description = $this->chat->storage()->get('temp_description');

        if (empty($title) || empty($description)) {
            $this->reply("Failed to create task. Please try again..");
            return;
        }

        try {
            $request = new Request([
                'telegram_id' => $this->message->from()->id(),
                'title' => $title,
                'description' => $description,
                'status' => 'pending'
            ]);

            $response = $this->taskController->createTask($request);
            $task = $response->getData();

            $this->reply("✅ Task created: {$task->title}");

            // Clear temporary storage
            $this->chat->storage()->forget('temp_title');
            $this->chat->storage()->forget('temp_description');

        } catch (\Exception $e) {
            Log::error("Ошибка при создании задачи", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->user->id ?? null
            ]);
            $this->reply("An error occurred while creating the task. Please try again.!");
        }
    }

    public function create_task()
    {
        if (!$this->user) {
            $this->user = $this->authorizeUser();
            if (!$this->user) return;
        }
        // Start the process by asking for title
        $this->askTitle();
    }

    public function update_task()
{
    if (!$this->user) {
        $this->user = $this->authorizeUser();
        if (!$this->user) return;
    }
    try {
        $request = new Request(['user_id' => $this->user->id]);
        $response = $this->taskController->getUserTasks($request);
        $tasks = $response->getData();

        if (empty($tasks)) {
            $this->chat->html("You have no tasks to change.")->send();
            return;
        }

        $keyboard = Keyboard::make();
        foreach ($tasks as $task) {
            $keyboard->button("✏️ ID: {$task->id} - " . mb_substr($task->title, 0, 30))
                ->action('selectTaskForUpdate') 
                ->param('task_id', $task->id);
        }

        $keyboard->button("❌ Cancel")->action('handleCancelButton');

        $this->chat->html("Select a task to edit:")->keyboard($keyboard)->send();

     //   Log::info("Показан список задач для изменения пользователю {$this->user->telegram_id}");
    } catch (\Exception $e) {
        Log::error("Ошибка при получении списка задач для изменения", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->chat->html("There was an error loading the task list. Please try again.!")->send();
    }
}
    public function delete_task()
    {
        Log::info("function delete_task --- start");

        if (!$this->user) {
            $this->user = $this->authorizeUser();
            if (!$this->user) return;
        }

        try {
            $request = new Request(['user_id' => $this->user->id]);
            $response = $this->taskController->getUserTasks($request);
            $tasks = $response->getData();

            if (empty($tasks)) {
                $this->chat->html("You have no tasks to delete..")->send();
                return;
            }

            $keyboard = Keyboard::make();
            foreach ($tasks as $task) {
                $keyboard->button("🗑️ ID: {$task->id} - " . mb_substr($task->title, 0, 30))
                    ->action('confirmDeleteTask')
                    ->param('task_id', $task->id);
            }

            $keyboard->button("❌ Cancel")->action('handleCancelButton');
            $this->chat->html("Select a task to delete:")->keyboard($keyboard)->send();
          //  Log::info("Показан список задач для удаления пользователю {$this->user->telegram_id}");
        } catch (\Exception $e) {
            Log::error("Ошибка при получении списка задач для удаления", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->chat->html("There was an error loading the task list. Please try again.!")->send();
        }
    }

    public function confirmDeleteTask(): void
    {
        $taskId = $this->data->get('task_id');
        if (!$taskId) {
            $this->chat->html("Error: Task ID not specified.")->send();
            return;
        }
        try {
            $request = new Request([
                'task_id' => $taskId,
                'user_id' => $this->user->id,
                'is_admin' => $this->user->is_admin
            ]);

            $result = $this->taskController->deleteTask($request);
            if ($result->getStatusCode() === 200) {
                $this->chat->html("✅ Task ID {$taskId} successfully removed.")->send();
                Log::info("Task ID {$taskId} удалена через callback пользователем {$this->user->telegram_id}");
            } else {
                $data = $result->getData();
                $this->chat->html("❌ " . ($data->message ?? "Error deleting task."))->send();
            }
        } catch (\Exception $e) {
            Log::error("Ошибка при удалении задачи", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->chat->html("An error occurred while deleting the task.")->send();
        } finally {
            $this->removeKeyboard();
        }
    }

    public function handleCallbackQuery(): void
    {
        // Авторизация пользователя
        $this->user = $this->authorizeUser();
        if (!$this->user) return;
        // Вызов стандартной обработки библиотеки
        parent::handleCallbackQuery();
    }

    public function list_tasks()
    {
        // Авторизация уже пройдена в handleTextCommand
        if (!$this->user) {
            $this->user = $this->authorizeUser();
            if (!$this->user) return;
        }

        try {
            // Делегируем получение задач контроллеру
            $request = new Request([
                'user_id' => $this->user->id,
                'is_admin' => $this->user->is_admin
            ]);

            $response = $this->taskController->getUserTasks($request);
            $tasks = $response->getData();

            if (empty($tasks)) {
                $this->reply("Tasks not found.");
                return;
            }

            $response = "📋 Your tasks:\n\n";
            foreach ($tasks as $task) {
                $statusEmoji = $this->getStatusEmoji($task->status);
                $response .= "🆔 ID: {$task->id}\n";
                $response .= "📝 Headline: {$task->title}\n";
                if ($task->description) {
                    $response .= "📄 Description: " . mb_substr($task->description, 0, 100) . "\n";
                }
                $response .= "📊 Status: {$statusEmoji} {$task->status}\n";
                $response .= "─────────────────\n";
            }

            $this->reply($response);
        } catch (\Exception $e) {
            Log::error("Ошибка при получении списка задач", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->reply("There was an error loading tasks. Try again.!");
        }
    }

    private function getStatusEmoji($status)
    {
        return match ($status) {
            'pending' => '⏳',
            'in_progress' => '🔄',
            'completed' => '✅',
            default => '❓'
        };
    }

    public function handle(Request $request, TelegraphBot $bot): void
    {
        $this->bot = $bot;
       
        // Вызываем родительский handle для инициализации
        parent::handle($request, $bot);

        // Проверяем наличие callback-запроса или сообщения
        if ($this->callbackQuery) {
            Log::info("Входящий запрос в handle: callbackQuery ");
            return; // Обработка уже произведена в handleCallbackQuery()
        }
        if ($this->message) {
            Log::info("Входящий запрос в handle: message ");
            return; // Обработка уже произведена в соответствующих методах handle<CommandName>()
        }
        else{
            Log::info("Входящий запрос в handle:  ----- else ---- Неизвестный тип запроса");
        }
    }

    public function handleCancelButton(): void
    {
        $this->chat->html("❌ Operation canceled.")->send();
        $this->removeKeyboard();
    }

    public function selectTaskForUpdate(): void
{
    $taskId = $this->data->get('task_id');
    if (!$taskId) {
        $this->chat->html("Error: Task ID not specified.")->send();
        return;
    }

    // Сохраняем task_id в storage
    $this->chat->storage()->set('update_task_id', $taskId);

    // Создаем клавиатуру для выбора поля
    $keyboard = Keyboard::make()
        ->button("📝 Headline")->action('chooseFieldToUpdate')->param('field', 'title')
        ->button("📄 Description")->action('chooseFieldToUpdate')->param('field', 'description')
        ->button("📊 Status")->action('chooseFieldToUpdate')->param('field', 'status')
        ->button("❌ Cancel")->action('handleCancelButton');

    $this->chat->html("Select what you want to change:")->keyboard($keyboard)->send();
    
    // Удаляем предыдущую клавиатуру
    $this->removeKeyboard();
}

public function chooseFieldToUpdate(): void
{
    $field = $this->data->get('field');
    $taskId = $this->chat->storage()->get('update_task_id');
    
    if (!$field || !$taskId) {
        $this->chat->html("Error: Data not received.")->send();
        return;
    }

    // Сохраняем выбранное поле в storage
    $this->chat->storage()->set('update_field', $field);

    switch ($field) {
        case 'title':
            $this->chat->html("Enter a new task title:")->send();
            $this->chat->storage()->set('next_step', 'ask_title');
            break;
            
        case 'description':
            $this->chat->html("Enter a new task description:")->send();
            $this->chat->storage()->set('next_step', 'ask_description');
            break;
            
        case 'status':
            $this->askNewStatus();
            break;
            
        default:
            $this->chat->html("Unknown field to change.")->send();
    }
    
    $this->removeKeyboard();
}

protected function askNewStatus()
{
    $keyboard = Keyboard::make()
        ->button("⏳ Waiting")->action('updateTaskStatus')->param('status', 'pending')
        ->button("🔄 In progress")->action('updateTaskStatus')->param('status', 'in_progress')
        ->button("✅ Completed")->action('updateTaskStatus')->param('status', 'completed')
        ->button("❌ Cancel")->action('handleCancelButton');

    $this->chat->html("Select a new task status:")->keyboard($keyboard)->send();
}


public function updateTaskStatus(): void
{
    $status = $this->data->get('status');
    $taskId = $this->chat->storage()->get('update_task_id');
    $userId = $this->user->id;

    if (!$taskId || !$status) {
        $this->chat->html("Error: Data not received.")->send();
        return;
    }

    try {
        $request = new Request([
            'task_id' => $taskId,
            'user_id' => $userId,
            'status' => $status
        ]);

        $result = $this->taskController->updateTask($request);

        if ($result->getStatusCode() === 200) {
            $this->chat->html("✅ Task status ID {$taskId} successfully updated to '{$status}'!")->send();
        } else {
            $data = $result->getData();
            $this->chat->html("❌ Error: " . ($data->message ?? "Failed to update task status"))->send();
        }

        // Очищаем storage
        $this->chat->storage()->forget('update_task_id');
        $this->chat->storage()->forget('update_field');

    } catch (\Exception $e) {
        Log::error("Ошибка при обновлении статуса задачи", [
            'error' => $e->getMessage(),
            'task_id' => $taskId
        ]);
        $this->reply("An error occurred while updating the task status. Please try again.!");
    } finally {
        $this->removeKeyboard();
    }
}


    protected function updateTaskFromStorage($taskId)
    {
        $title = $this->chat->storage()->get('temp_title');
        $description = $this->chat->storage()->get('temp_description');
        $userId = $this->chat->storage()->get('update_user_id');
        Log::info("updateTaskFromStorage  title $title   description $description   userId $userId");

        try {
            $request = new Request([
                'task_id' => $taskId,
                'user_id' => $userId,
         //       'is_admin' => $this->user->is_admin,
                'title' => $title,
                'description' => $description
            ]);

            $result = $this->taskController->updateTask($request);

            if ($result->getStatusCode() === 200) {
                $this->chat->html("✅ Task ID {$taskId} successfully updated!")->send();
            } else {
                $data = $result->getData();
                $this->chat->html("❌ Error: " . ($data->message ?? "Failed to update task"))->send();
            }

            // Очищаем storage
            $this->chat->storage()->forget('update_task_id');
            $this->chat->storage()->forget('temp_title');
            $this->chat->storage()->forget('temp_description');

        } catch (\Exception $e) {
            Log::error("Ошибка при обновлении задачи", [
                'error' => $e->getMessage(),
                'task_id' => $taskId
            ]);
            $this->reply("An error occurred while updating the task. Please try again.!");
        }
    }
    private function handleCancelUpdate()
    {
        $this->chat->storage()->forget('update_task_id');
        $this->chat->html("❌ Task update cancelled.")->send();
        $this->removeKeyboard();
    }

    private function removeKeyboard()
    {
        try {
            $this->chat->replaceKeyboard(
                $this->callbackQuery->message()->id(),
                Keyboard::make()
            )->send();
        } catch (\Exception $e) {
            Log::warning("Не удалось удалить клавиатуру: " . $e->getMessage());
        }
    }

    private function handleTextCommand()
    {
        $this->user = $this->authorizeUser();
        if (!$this->user) return;

        $command = $this->message->text();
        Log::debug("Входящий запрос в handle: $command");

        if (!str_starts_with($command, '/')) {
            return;
        }

        $commandParts = explode(' ', ltrim($command, '/'));
        $commandName = $commandParts[0];
        $args = array_slice($commandParts, 1);

        if (!method_exists($this, $commandName)) {
            $this->chat->html(
                "❌ Неизвестная команда.\n\n" .
                "📋 Available commands:\n" .
            "/help - all teams\n" .
            "/start - welcome and registration\n" .
            "/list_tasks - show tasks\n" .
            "/create_task - create a task\n" .
            "/delete_task - delete task\n" .
            "/update_task - update task\n" .
            "/filter_tasks - filter tasks"
            )->send();
            return;
        }

        // Вызываем команду с аргументами (авторизация уже пройдена)
        $this->$commandName(...$args);
    }
    public function handleFilterByStatus(): void
    {
        $this->user = $this->authorizeUser();

        $usrIdStatus = $this->data->get('status');
        if (!$usrIdStatus) {
            $this->chat->html("Error: Task ID not specified.")->send();
            return;
        }

        try {
            $usrId = $this->user->id;
            $usrIdAdmin = $this->user->is_admin;

            $request = new Request([
                'user_id' => $this->user->id,
                'is_admin' => $this->user->is_admin,
                'status' => $usrIdStatus,
            ]);
            Log::info("---handleFilterByStatus usrId $usrId usrIdAdmin $usrIdAdmin  usrIdStatus $usrIdStatus user_id ");

            $response = $this->taskController->getFilteredTasks($request);
            $tasks = $response->getData();

            $statusNames = [
                'pending' => 'Waiting',
                'in_progress' => 'In progress',
                'completed' => 'Completed'
            ];

            if (empty($tasks)) {
                $this->chat->html("📋 Tasks with status '{$statusNames[$usrIdStatus]}' not found.")->send();
            } else {
                $this->displayFilteredTasks($tasks, "Tasks with status: {$statusNames[$usrIdStatus]}");
            }

            $this->removeKeyboard();
        } catch (\Exception $e) {
            Log::error("Ошибка при фильтрации задач по статусу", ['error' => $e->getMessage()]);
            $this->reply("An error occurred while filtering tasks. Please try again.!");
        }
    }

    public function handleStartTextSearch(): void
    {
        $this->chat->storage()->forget('next_step');
        $this->chat->storage()->set('next_step', 'text_search');
        $this->chat->html("🔍 Enter text to search in task titles and descriptions:")->send();
        $this->removeKeyboard();
    }

    public function handleCancelFilter(): void
    {
        $this->chat->html("❌ Filtering cancelled.")->send();
        $this->removeKeyboard();
    }
    public function filter_tasks()
    {
        if (!$this->user) {
            $this->user = $this->authorizeUser();
            if (!$this->user) return;
        }

        $keyboard = Keyboard::make()
            ->button("⏳ Waiting")->action('handleFilterByStatus')->param('status', 'pending')
            ->button("🔄 In progress")->action('handleFilterByStatus')->param('status', 'in_progress')
            ->button("✅ Completed")->action('handleFilterByStatus')->param('status', 'completed')
            ->button("🔍 Search by text")->action('handleStartTextSearch')
            ->button("❌ Cancel")->action('handleCancelFilter');

        $this->chat->html("Select a filter for tasks:")->keyboard($keyboard)->send();
    }

}
