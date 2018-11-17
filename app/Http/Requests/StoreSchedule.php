<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSchedule extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date',
        ];
    }

    public function persist()
    {
        $user = Auth::user();

        $schedule = $user-> schedules()-> create([
            'name' => request('name'),
            'start' => request('start'),
            'end' => request('end'),
        ]);

        foreach (request('module') as  $key => $value){
            $schedule-> modules()-> create([
                'name' => $value
            ]);
        }
    }
}