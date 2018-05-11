<?php

namespace App\Http\Controllers;

use App\EmployeeProfile;
use App\Experience;
use App\Language;
use App\Skill;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class CVController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = EmployeeProfile::all();
        foreach ($employees as $key => $employee) {
            $employees[$key]->position = $employee->experiences[0]->position;
        }

        return view('cv.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $selectSkills      = Skill::pluck('skill', 'id');
        $selectLanguages   = Language::pluck('language', 'id');
        $selectExperiences = Experience::pluck('position', 'id');

        return view('cv.create', compact('selectSkills', 'selectLanguages', 'selectExperiences'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function store(Request $request)
    {
        //dd($request->input());
        $input     = Input::all();
        $rules     = [
            'name'             => 'required|max:255',
            'lastname'         => 'required|max:255',
            'address'          => 'max:255',
            'email'            => 'required|email',
            'phone'            => 'numeric|',
            'avatar'           => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'skill'            => 'required|numeric',
            'percentage_skill' => 'required|numeric|min:10|max:100',
            'language'         => 'required|numeric',
            'percentage_lang'  => 'required|numeric|min:10|max:100',
            'experience'       => 'required|numeric',
            'company'          => 'required|max:255',
            'description'      => 'required|max:655',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date',
        ];
        $validator = Validator::make($input, $rules);
        if ($validator->passes()) {
            try {
                DB::beginTransaction();

                $avatarFile      = $input['avatar'];
                $input['avatar'] = $avatarFile->getClientOriginalName();

                $newEmployee = EmployeeProfile::create($input);
                $newEmployee->skills()->sync([$request['skill'] => ['percentage' => $request['percentage_skill']]]);
                $newEmployee->languages()
                    ->sync([$request['language'] => ['percentage' => $request['percentage_lang']]]);
                $newEmployee->experiences()->sync([
                    $request['experience'] => [
                        'company'     => $request['company'],
                        'description' => $request['description'],
                        'start_date'  => $request['start_date'],
                        'end_date'    => $request['end_date'],
                    ],
                ]);

                $avatarFile->move(public_path('avatars/'), $input['avatar']);

                DB::commit();

                $return = redirect()->route('cv.index')->with('status', 'CV added successfully');
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } else {
            $return = redirect()->route('cv.create')->withErrors($validator->getMessageBag());
        }

        return $return;


    }

    /**
     * Display the specified resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee          = EmployeeProfile::find($id);
        $employeeSkills    = $employee->skills;
        $employeeLanguages = $employee->languages;

        $employeeExperiences = [];
        foreach ($employee->experiences as $key => $experience) {
            $employeeExperiences[$key]      = $experience;
            $employeeExperiences[$key]->job = $experience->pivot;
        }

        //$employeeExperiences[0]->job->company;
        return view('cv.show', compact('employee', 'employeeSkills', 'employeeLanguages', 'employeeExperiences'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $employee = EmployeeProfile::find($id);

        $selectSkills     = $employee->skills->pluck('skill', 'id');
        $percentageSkills = [];
        foreach ($employee->skills as $skill) {
            $percentageSkills[$skill->pivot->skill_id] = $skill->pivot->percentage;
        }

        $selectLanguages     = $employee->languages->pluck('language', 'id');
        $percentageLanguages = [];
        foreach ($employee->languages as $language) {
            $percentageLanguages[$language->pivot->language_id] = $language->pivot->percentage;
        }

        $selectExperiences = $employee->experiences->pluck('position', 'id');
        $experiences       = [];
        foreach ($employee->experiences as $experience) {
            $experiences[$experience->pivot->experience_id] = $experience->pivot;
        }

        $selectSkillsHidden = Skill::all()->pluck('skill', 'id');

        return view('cv.edit',
            compact('employee', 'selectSkills', 'selectLanguages', 'percentageSkills', 'percentageLanguages',
                'selectExperiences', 'experiences',
                'selectSkillsHidden'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        //dd($request->all());
        $input = Input::all();
        $rules = [
            'name'               => 'required|max:255',
            'lastname'           => 'required|max:255',
            'address'            => 'max:255',
            'email'              => 'required|email',
            'phone'              => 'numeric|',
            'skill.*'            => 'required|numeric',
            'percentage_skill.*' => 'required|numeric|min:10|max:100',
            'language.*'         => 'required|numeric',
            'percentage_lang.*'  => 'required|numeric|min:10|max:100',
            'experience.*'       => 'required|numeric',
            'company.*'          => 'required|max:255',
            'description.*'      => 'required|max:655',
            'start_date.*'       => 'required|date',
            'end_date.*'         => 'required|date',
        ];
        if ($request->hasFile('avatar')) {
            $rules['avatar'] = 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048';
        } else {
            $input['avatar'] = EmployeeProfile::find($id)->avatar;
        }

        $validator = Validator::make($input, $rules);
        if ($validator->passes()) {
            try {
                DB::beginTransaction();
                if ($request->hasFile('avatar')) {
                    $avatarFile      = $input['avatar'];
                    $input['avatar'] = $avatarFile->getClientOriginalName();
                    $avatarFile->move(public_path('avatars/'), $input['avatar']);
                }

                $updateEmployeeCV = EmployeeProfile::find($id);
                $updateEmployeeCV->update($input);

                //Sincronizar la tabla pivot, recorro los array skill y percentage
                //Para agregar nuevo skill,
                // condición(cout skills from form < cout skill from BD) ? sync() :
                // condicion(cout skills from form == cout skill from BD) ? updateExistingPivot() :
                // condicion(cout skills from form > cout skill from BD) ? attach(), sync()
                foreach ($request['skill'] as $key => $value) {
                    $updateEmployeeCV->skills()
                        ->updateExistingPivot($request['skill'][$key],
                            ['percentage' => $request['percentage_skill'][$key]]);
                }

                //Sincronizar la tabla pivot, recorro los array language y percentage
                foreach ($request['language'] as $key => $value) {
                    $updateEmployeeCV->languages()
                        ->updateExistingPivot($request['language'][$key],
                            ['percentage' => $request['percentage_lang'][$key]]);
                }

                //Sincronizar la tabla pivot, recorro los array experiencia
                foreach ($request['experience'] as $key => $value) {
                    $updateEmployeeCV->experiences()
                        ->updateExistingPivot($request['experience'][$key],
                            [
                                'company'     => $request['company'][$key],
                                'description' => $request['description'][$key],
                                'start_date'  => $request['start_date'][$key],
                                'end_date'    => $request['end_date'][$key],
                            ]);
                }

                DB::commit();

                $return = redirect()->route('cv.index')->with('status', 'CV modified successfully');
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } else {
            $return = redirect()->route('cv.edit', [$id])->withErrors($validator->getMessageBag());
        }

        return $return;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleteEmployeeCV = EmployeeProfile::find($id);
        $deleteEmployeeCV->skills()->sync([]);
        $deleteEmployeeCV->languages()->sync([]);
        $deleteEmployeeCV->experiences()->sync([]);
        $deleteEmployeeCV->delete();

        return redirect()->route('cv.index')->with('status', 'CV deleted successfully');
    }
}
