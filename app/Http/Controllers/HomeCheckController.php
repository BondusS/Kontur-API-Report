<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporter;

class HomeCheckController extends Controller
{
    public function check(Request $request){
        $report = new Reporter();
        $report->key = $request->input(key: 'key');
        $report->inn = $request->input(key: 'inn');
        $data = $report->returndata($report->key, $report->inn);
        $report->text = '';
        $report->printing($data);
        /*dd($report->text);*/
        return view(view: 'report', data: ['text' => $report->text]);
    }
}
