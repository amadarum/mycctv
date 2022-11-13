<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Camera;
use App\Models\Capture;

class UploadProcessor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'capture:process';

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

    private function parseDuration($str) {
        $el = explode(':', $str);
        if (count($el) != 3) {
            return false;
        }
        $total = $el[0]*60*60*100;
        $total += $el[1]*60*100;
        $el = explode(',',$el[2]);
        return $total + $el[0]*100;
    }
    private static function parseCaptureAt($str) {
        $y = explode('Y',$str,2);
        $m = explode('M',$y[1],2);
        $y = $y[0];
        $d = explode('D',$m[1],2);
        $m = $m[0];
        $h = explode('H',$d[1],2);
        $d = $d[0];

        $i = explode('M',$h[1],2);
        $h = $h[0];
        $s = explode('S',$i[1],2)[0];
        $i = $i[0];

        return "$y-$m-$d $h:$i:$s";
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = storage_path().'/upload/';
        $files = scandir($path);
        $cameras = null;
        foreach($files as $file) {
            if ('.' == $file || '..' == $file) {
                continue;
            }
            $fs = explode('_',$file,2);
            $src = $path.$file;
            if (count($fs) < 2) {
                unlink($src);
                continue;
            }
            $hostname = $fs[0];
            $filename = $fs[1];
            if (isset($cameras[$hostname])) {
                $camera = $cameras[$hostname];
            } else {
                $camera = Camera::where('hostname',$hostname)->first();
                if (!$camera) {
                    echo "Camera '$hostname' not found\n";
                    unlink($src);
                    continue;
                }
                $cameras[$hostname] = $camera;
            }
            $fname = explode('.',$filename)[0];
            $capture = Capture::where('camera_id',$camera->id)->where('filename',$fname)->first();
            if ($capture) {
                echo "Capture '$hostname' $filename exist\n";
                unlink($src);
                continue;
            }
            $basecommand = "ffmpeg  -i '".escapeshellarg($src)."'";
            $command = $basecommand." 2>&1|grep Duration:|awk '{print $2}'";
            $sduration = exec($command);
            $duration = self::parseDuration($sduration);
            if (!$duration)  {
                echo "Capture '$hostname' $filename duration $sduration\n";
                unlink($src);
                continue;
            }
            
            $video_path = storage_path().'/internal/videos/'.$hostname;
            $thumb_path = storage_path().'/internal/thumbnail/'.$hostname;
            @mkdir($video_path);
            @mkdir($thumb_path);
            $capture_time = $duration % 60;
            if ($capture_time<10) $capture_time = "0${capture_time}";

            $thumb_name = "$thumb_path/$fname.png";

            $command = 'yes|'.$basecommand." -ss 00:00:$capture_time -frames:v 1 $thumb_name 2>&1";
            $rc = 0;
            exec($command, null, &$rc);
            if ($rc == 0) {
                $command = "convert -resize 50% $thumb_name $thumb_name";
                exec($command, null, &$rc);
            } else {
                echo "$file get thumbe faield\n";
            }
            echo "$file $sduration $duration\n";
            $capture = new Capture();
            $capture->camera_id = $camera->id;
            $capture->filename = $fname;
            $capture->path = $hostname;
            $capture->duration = $duration;
            $capture->created_at = self::parseCaptureAt($filename);
            $capture->save();
            rename($src,$video_path.'/'.$filename);
        }
        return 0;
    }
}
