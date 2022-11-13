<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Camera;
use App\Models\Capture;

class ArchiveCapture extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'capture:archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $total = 0;
        Camera::orderBy('id')->chunk(100,function($cameras) use(&$total) {
            $total_camera = 0;
            foreach($cameras as $camera) {
                if (!$camera->keep_days) {
                    continue;
                }
                $video_path = storage_path().'/internal/videos/'.$camera->hostname.'/';
                $thumb_path = storage_path().'/internal/thumbnail/'.$camera->hostname.'/';
                $expired = date('Y-m-d H:i:s',time()-$camera->keep_days*24*60*60);

                Capture::where('camera_id',$camera->id)->where('created_at','<',$expired)->orderBy('id')->chunk(100, function($captures) use($video_path,$thumb_path, &$total_camera) {
                    foreach($captures as $capture) {
                        $total_camera++;
                        @unlink($video_path.$capture->filename.'.mp4');
                        @unlink($thumb_path.$capture->filename.'.png');
                        echo "$capture->filename \n";
                        $capture->delete();
                    }
                });
            }
            $total += $total_camera;
            echo "$camera->hostname deleted:$total_camera\n";
        });
        echo "finished deleted:$total\n";
        return 0;
    }
}
