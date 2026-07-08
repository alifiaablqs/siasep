<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Director extends Model
{
    protected $table = 'director';
    protected $primaryKey = 'id_director';
    protected $fillable = ['name_director', 'kode_director', 'parent_director_id', 'is_main', 'is_active'];

    public $timestamps = false;

    public function subDirectors()
    {
        return $this->hasMany(Director::class, 'parent_director_id', 'id_director');
    }

    public function parentDirector()
    {
        return $this->belongsTo(Director::class, 'parent_director_id', 'id_director');
    }

    public function divisi()
    {
        return $this->hasMany(Divisi::class, 'director_id_director', 'id_director');
    }

    public function department()
    {
        return $this->hasMany(Department::class, 'director_id_director', 'id_director');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'director_id_director', 'id_director');
    }
}
