<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Camera;

class CameraController extends Controller
{
    public function index(Request $request) {

    }

    public function create(Request $request) {
        return view('camera.create');
    }

    public function store(Request $request) {
        $request->validate([
            'hostname' => 'required|unique:cameras,hostname|max:255',
            'name' => 'required|max:255',
            'keep_days' => 'required|integer'
        ]);

        $camera = new Camera();
        $camera->hostname = $request->hostname;
        $camera->name = $request->name;
        $camera->keep_days = $request->keep_days;

        $camera->save();
        return redirect('/dashboard');
    }
    
}