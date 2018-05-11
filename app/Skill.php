<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{

    protected $fillable
        = [
            'skill',
        ];

    public function employees()
    {
        return $this->belongsToMany(EmployeeProfile::class, 'employee_skill', 'skill_id', 'employee_id')
            ->withPivot('percentage')
            ->withTimestamps();
    }
}
