<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $fillable
        = [
            'position',
        ];

    public function employees(){
        return $this->belongsToMany(EmployeeProfile::class, 'employee_experience', 'experience_id', 'employee_id')
            ->withPivot('company', 'description', 'start_date', 'end_date')
            ->withTimestamps();
    }
}
