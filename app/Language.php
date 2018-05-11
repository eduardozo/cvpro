<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{

    protected $fillable
        = [
            'language',
        ];

    public function employees(){
        return $this->belongsToMany(EmployeeProfile::class, 'employee_language', 'language_id', 'employee_id')
            ->withPivot('percentage')
            ->withTimestamps();
    }
}
