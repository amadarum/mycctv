<x-app-layout>
<x-slot name="style">
    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <a href="/camera/create"><i class="fa fa-plus" aria-hidden="true"></i> new camera</a>
                <div class="p-6 bg-white border-b border-gray-200">
                    @foreach($cameras as $camera)
                    <div>
                        <h2><a href="/capture/{{$camera->id}}">{{$camera->hostname}}</a></h2>
                        <img src="/thumbnail/{{$camera->thumbnail}}" style="width:60%">
                        
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <x-slot name="script">
    </x-slot>
</x-app-layout>
