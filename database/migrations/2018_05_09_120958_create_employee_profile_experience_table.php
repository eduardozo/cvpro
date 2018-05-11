<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeProfileExperienceTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_experience', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('experience_id')->unsigned();
            $table->integer('employee_id')->unsigned();
            $table->string('company');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('experience_id')->references('id')->on('experiences');
            $table->foreign('employee_id')->references('id')->on('employee_profiles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_experience');
    }
}
