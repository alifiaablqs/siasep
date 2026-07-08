<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'unit';
    protected $primaryKey = 'id_unit';
    protected $fillable = ['name_unit', 'department_id_department', 'section_id_section', 'is_active'];

    public $timestamps = false;

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id_department');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id_section');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'unit_id_unit');
    }
}
