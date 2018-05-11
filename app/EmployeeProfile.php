<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{

    protected $fillable
        = [
            'name',
            'lastname',
            'address',
            'email',
            'phone',
            'avatar',
        ];

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'employee_skill', 'employee_id', 'skill_id')
            ->withPivot('percentage')
            ->withTimestamps();
    }

    public function languages(){
        return $this->belongsToMany(Language::class, 'employee_language', 'employee_id', 'language_id')
            ->withPivot('percentage')
            ->withTimestamps();
    }

    public function experiences(){
        return $this->belongsToMany(Experience::class, 'employee_experience', 'employee_id', 'experience_id')
            ->withPivot('company', 'description', 'start_date', 'end_date')
            ->withTimestamps();
    }
}
