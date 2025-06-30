<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskGroupNotification extends Model
{
    protected $table = 'task_group_notifications';

    protected $fillable = ['task_id', 'group_chat_id'];

    public $timestamps = true;

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function groupChat()
    {
        return $this->belongsTo(GroupChat::class);
    }
}
