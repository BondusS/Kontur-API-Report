<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporter;

class HomeCheckController extends Controller
{
    public function check(Request $request){
        $report = new Reporter(key: $request->input(key: 'key'), inn: $request->input(key: 'inn'));
        $data = $report->returndata();
        $text = $report->getOrganizationReqHTML($data[0]);
        return view(view: 'report', data: ['text' => $text]);
    }
}
