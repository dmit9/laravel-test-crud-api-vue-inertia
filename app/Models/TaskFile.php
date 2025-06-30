<?php

namespace App\Models;

use Illuminate\Console\View\Components\Task;
use Illuminate\Database\Eloquent\Model;

class TaskFile extends Model
{
    protected $fillable = ['task_id', 'file_path', 'file_name', 'file_type'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
