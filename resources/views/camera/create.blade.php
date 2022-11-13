<x-app-layout>
    <x-slot name="style">

    </x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Camera') }}
        </h2>
    </x-slot>

    <div class="py-12">    
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 bg-white border-b border-gray-200">
                    <h1>Create Camera</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="/camera" method="post">
                    @csrf
                        <div class="p-10">
                            <div class="grid grid-cols-4 gap-4">
                                <label class="col-auto">Hostname</label>
                                <input  class="col-auto" type="text" name="hostname" value="{{ old('hostname') }}"/>
                            
                                <label class="col-auto">Name</label>
                                <input class="col-auto" type="text" name="name"  value="{{ old('name') }}"/>
                            
                                <label class="col-auto">Keep (days)</label>
                                <input class="col-auto" type="text" name="keep_days"  value="{{ old('keep_days') }}"/>
                            </div>
                            
                            <button>Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-slot name="script">
        
    </x-slot>
</x-app-layout>

