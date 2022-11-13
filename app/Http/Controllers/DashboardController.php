<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Camera;
use App\Models\Capture;

class DashboardController extends Controller
{
    public function index(Request $request) {
        $cameras = Camera::get();
        foreach($cameras as &$camera) {
            $capture = Capture::where('camera_id',$camera->id)->orderBy('id','desc')->first();
            if ($capture) {
                $camera->thumbnail = $camera->hostname.'/'.$capture->filename.'.png';
            }
        }
        return view('dashboard',[
            'cameras' => $cameras
        ]);
    }

    public function thumbnail($hostname, $filename) {
        $path = $hostname.'/'.$filename;
    
        return response(null)
            ->header('Content-type', 'image/png')
            ->header('X-Accel-Redirect', "/internal/thumbnail/$path")
            ->header('X-Sendfile', "/internal/thumbnail/$path");
    }

    public function video($hostname, $filename) {
        $path = $hostname.'/'.$filename;
        return response(null)
            ->header('Content-type', 'image/png')
            ->header('X-Accel-Redirect', "/internal/videos/$path")
            ->header('X-Sendfile', "/internal/videos/$path");
    }

    public function camera($id, Request $request) {
        $camera = Camera::find($id);
        if (!$camera) {
            abort(404);
        }
        $date = $request->input('date');

        $captures = Capture::where('camera_id',$camera->id)->orderBy('created_at');
        if ($date) {
            $captures->whereBetween('created_at',[$date." 00:00:00",$date." 23:59:59"]);
        } else {
            $date = date('Y-m-d');
            $captures->where('created_at','>',date('Y-m-d H:i:s',time()-24*60*60));
        }
        $captures = $captures->get();
        foreach($captures as &$capture) {
            $capture->video = '/video/'.$camera->hostname.'/'.$capture->filename.'.mp4';
            $capture->captured_at = strtotime($capture->created_at);
            //Log::debug("$capture->captured_at $capture->created_at");
        }
        return view('camera',[
            'date' => $date,
            'cameras' => $camera,
            'captures' => $captures
        ]);
    }
}