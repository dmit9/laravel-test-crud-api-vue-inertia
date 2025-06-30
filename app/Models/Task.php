<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'status'];

    public function user()
    {
        return $this->belongsTo(TelegramUser::class);
    }
    public function files()
    {
        return $this->hasMany(TaskFile::class);
    }
    public function groupChats()
    {
        return $this->belongsToMany(GroupChat::class, 'task_group_notifications');
    }
}
