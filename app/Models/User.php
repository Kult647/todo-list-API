<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Связь один-к-одному с профилем
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    // Связь один-ко-многим с задачами (автор)
    public function tasks()
    {
        return $this->hasMany(Task::class, 'author_id');
    }

    // Связь многие-ко-многим с проектами
    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    // Связь многие-ко-многим с задачами (соисполнители)
    public function sharedTasks()
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }
}
