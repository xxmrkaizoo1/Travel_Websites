<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\TravelOverview;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(TravelOverview $overview): View
    {
        return view('Admin.destinations.index', [
            'destinations' => $overview->destinations(),
        ]);
    }
}
