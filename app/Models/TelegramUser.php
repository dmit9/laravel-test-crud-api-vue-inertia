<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    protected $table = 'telegram_users';
    protected $fillable = ['telegram_id', 'username', 'first_name', 'last_name', 'is_admin'];
    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id');
    }
}
