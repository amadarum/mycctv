<x-app-layout>
    <x-slot name="style">
        <style>
            * {
  box-sizing: border-box;
}


#lineCont {
    margin-top:10px;
  width: 100%;
  height: 20%;
}

#line {
  height: 6px;
  width: 90%;
  background: white;
  border-radius: 5px;
  margin: auto;
  top: 50%;
  transform: translateY(-50%);
  position: relative;
  background:#555;
}

#span {
  width: 90%;
  margin: auto;
  margin-top: 1px;
  position: relative;
  color: red;
}

.circle_label {
    position: absolute;
    margin-left:-10px;

}

.circle {
  height: 20px;
  background: #e97162;
  border-radius: 1px;
  position: absolute;
  top: -7px;
  cursor: pointer;
  &:before {
    content: '';
    width: 10px;
    height: 10px;
    background: white;
    position: absolute;
    border-radius: 100%;
    top: 2px;
    left: 2px;
    display: none;
  }
  .popupSpan {
    width: auto;
    height: auto;
    padding: 10px;
    white-space: nowrap;
    display: none;
    color: #333;
    position: absolute;
    top: 20px;
    left: -75px;
    display: none;
    transition: all 0.1s ease-out;
  }
  // &:nth-child(odd) .popupSpan {
  //   top: 20px;
  // }
  &.hover:before, &.active:before {
    display: block;
  }
  &.hover .popupSpan, &.active .popupSpan {
    display: block;
  }
  &.active .popupSpan {
    top: -40px;
  }
}

.circleSel {
  height: 20px;
  background: blue;
  border-radius: 1px;
  position: absolute;
  top: -7px;
  cursor: pointer;
}


        </style>
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Video') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 bg-white border-b border-gray-200">
                    <div>
                        <form action="">
                            Date: <input type="text" id="datepicker" name="date" value="{{$date}}">
                            <button>Search</button>
                        </form>
                    </div>
                    <div id="lineCont"> 
                        <div id="span"></div>
                        <div id="line">
                          <div id="lineSel" class="circleSel" style="left:0%;width:0%;z-index:1000"></div>
                        </div>
                        
                    </div>
                    <div style="margin-top:10px">
                      <a onClick="next(-1)" href="#"><i class="fa fa-backward" aria-hidden="true"></i></a>
                      <span id="video_time2"></span>
                      <a  onClick="next(1)" href="#"><i class="fa fa-forward" aria-hidden="true"></i></a>
                    </div>
                    <video width="1280px" height="720px" id="myVideo">

                    </video>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="script">
        <script>
            var lineSelected=null;
            var lineWidth = 0
            var lineStart = 0
            var isPaused = true;
            var player = null
            var source = null
            var current_video = -1
            var videoTimeDiv = null
            var videoTimeStart = 0
            var videoDateStart = 0

            function play(video) {
                var duration = video.d * 100/ lineWidth
                if (duration < 0.1) duration = 0.1
                lineSelected.show()
                let css = {'left':((video.x - lineStart)*100/lineWidth) + '%',
                        'width': duration + '%'}
                lineSelected.css(css)
                $("#video_time").text(video.t)
                player.pause();
                source.setAttribute('src', video.v);
                player.load();
                player.play();
            }
            //Sample dates
let videos = [
    @foreach($captures as $c)
    { x:{{$c->captured_at}}, d:{{intVal($c->duration/100)}}, v:"{{$c->video}}", t:"{{$c->created_at}}"} ,
    @endforeach

];

//Format MM/DD/YYYY into string
function dateSpan(date) {

  return date.t.split(' ')[1]
}

//Main function. Draw your circles.

function makeTimelines() {
  //Forget the timeline if there's only one date. Who needs it!?
  if (videos.length == 0) {

  } else if (videos.length < 2) {
    $("#line").hide();
    $("#span").show().text(dateSpan(videos[0]));
    //This is what you really want.
  } else if (videos.length >= 2) {
    //Set day, month and year variables for the math
    var first = videos[0].x;
    lineStart = first
    var last = videos[videos.length - 1].x;

    lineWidth = last - first
    //Draw first date circle
    var duration = videos[0].d * 100/ lineWidth
    if (duration < 0.1) duration = 0.1
    $("#line").append('<div class="circle" id="circle' + 0 + '" style="left:0%;width:'+duration+'%"></div>');
    
    //Loop through middle dates
    var num = 10
    if ($("#line").width() < 400) {
      num = 3
    } else if ($("#line").width() < 600) {
      num = 6
    }
    let dw = 100/num
    var d = new Date(first*1000);
    let tz = - d.getTimezoneOffset()*60;
    var jj = parseInt(first) + tz
    var dd = parseInt(lineWidth / (num));
    var rem = 60 - (jj % 60)
    jj += rem
    dd += 60 - (dd % 60)
    i = parseInt(rem/lineWidth)
    var last2 = parseInt(last) + tz;

    for (; i < num && jj <= last2; i++,jj+=dd) {
        var h = parseInt(jj/60);
        var m = h % 60;
        if (m < 10) m = '0' + m
        h = parseInt(h/60) % 24;
        $("#span").append('<div class="circle_label" style="left: ' + i * dw + '%;">'+h+':'+m+':00</div>');
    }

    for (i = 1; i < videos.length - 1; i++) {
      /*var thisMonth = parseInt(dates[i].split('/')[0]);
      var thisDay = parseInt(dates[i].split('/')[1]);

      //Integer representation of the date*/
      var thisInt = videos[i].x - first

      //Integer relative to the first and last dates
      var relativeInt = thisInt *100/ lineWidth;
      duration = videos[i].d *100/ lineWidth
      if (duration < 0.1) duration = 0.1
      //Draw the date circle
      $("#line").append('<div class="circle" id="circle' + i + '" style="left: ' + relativeInt + '%;width:'+duration+'%"></div>');
      
    }

    //Draw the last date circle
    duration = videos[videos.length-1].d * 100/ lineWidth
    if (duration < 0.1) duration = 0.1
    $("#line").append('<div class="circle" id="circle' + i + '" style="left:99%;width:'+duration+'%"></div>');
    
  }

  $(".circle:first").addClass("active");
}


function selectDate(selector) {
  $selector = "#" + selector;
  $spanSelector = $selector.replace("circle", "span");
  var current = $selector.replace("circle", "");
  
  $(".active").removeClass("active");
  $($selector).addClass("active");
  
  if ($($spanSelector).hasClass("right")) {
    $(".center").removeClass("center").addClass("left")
    $($spanSelector).addClass("center");
    $($spanSelector).removeClass("right")
  } else if ($($spanSelector).hasClass("left")) {
    $(".center").removeClass("center").addClass("right");
    $($spanSelector).addClass("center");
    $($spanSelector).removeClass("left");
  };
  let num = selector.replace('circle','')
  current_video = parseInt(num)
  play(videos[current_video])
};


function next(d)
{
  if (d == -1) {
    if (current_video <=0 ){
      return
    }
  } else if (current_video >= videos.length -2 ) {
    return
  }
  current_video += d
  play(videos[current_video])
}
$( function() {
      lineSelected = $( "#lineSel" )
      videoTime =  $( "#video_time2" )
        $( "#datepicker" ).datepicker({
            dateFormat: "yy-mm-dd",
            defaultDate: "{{$date}}"
        });
        makeTimelines();

        $(".circle").mouseenter(function() {
            $(this).addClass("hover");
        });

        $(".circle").mouseleave(function() {
            $(this).removeClass("hover");
        });

        $(".circle").click(function() {
            var spanNum = $(this).attr("id");
            selectDate(spanNum);
        });

        player = document.getElementById("myVideo"); 
        source = document.createElement('source');
        
        source.setAttribute('type', 'video/mp4');
        player.appendChild(source)

        player.addEventListener("play", function() {
          isPaused = false
          videoDateStart = new Date().getTime();
          videoTimeStart = videos[current_video].x * 1000
          videoTime.text(
                new Date(videoTimeStart).toLocaleString())
        });
        player.addEventListener("pause", () => isPaused = true);

        let t = window.setInterval(function() {
            if(!isPaused) {
              let milisec = (new Date()).getTime() - videoDateStart;
              videoTime.text(
                new Date(videoTimeStart + milisec).toLocaleString())
            }
          }, 1000);
    } );
        </script>
    </x-slot>
</x-app-layout>
