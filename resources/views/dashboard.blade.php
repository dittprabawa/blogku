<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            @if (auth()->user()->role === 'admin')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 text-gray-900">
                        <a href="{{ route('admin.posts.index') }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                            &rarr; Buka CMS Admin (kelola posts, categories, tags)
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
