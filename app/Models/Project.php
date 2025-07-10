<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // Участники проекта
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Задачи проекта
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
