<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h1 class="text-2xl font-semibold mb-6">Dashboard</h1>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Card: Tasks assigned to me -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-medium">My Tasks</h2>
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $assignedTasks->count() }}</span>
                        </div>
                        <p class="text-gray-500 mb-4">Tasks assigned to you</p>
                        <a href="{{ route('tasks.index', ['assigned_to' => auth()->id()]) }}" class="text-blue-600 hover:text-blue-800 font-medium">View all →</a>
                    </div>
                    
                    <!-- Card: Overdue tasks -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-medium">Overdue Tasks</h2>
                            <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $overdueTasks->count() }}</span>
                        </div>
                        <p class="text-gray-500 mb-4">Tasks past their due date</p>
                        <a href="{{ route('tasks.index', ['overdue' => true]) }}" class="text-blue-600 hover:text-blue-800 font-medium">View all →</a>
                    </div>
                    
                    <!-- Card: My projects -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-medium">My Projects</h2>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $projects->count() }}</span>
                        </div>
                        <p class="text-gray-500 mb-4">Projects you're part of</p>
                        <a href="{{ route('projects.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">View all →</a>
                    </div>
                </div>
                
                <!-- Tasks assigned to me -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">Tasks Assigned to Me</h2>
                    @if($assignedTasks->isEmpty())
                        <p class="text-gray-500">No tasks assigned to you.</p>
                    @else
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul role="list" class="divide-y divide-gray-200">
                                @foreach($assignedTasks as $task)
                                <li>
                                    <a href="{{ route('tasks.show', $task) }}" class="block hover:bg-gray-50">
                                        <div class="px-4 py-4 sm:px-6">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-indigo-600 truncate">{{ $task->title }}</p>
                                                <div class="ml-2 flex-shrink-0 flex">
                                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $task->priority === 'high' ? 'bg-red-100 text-red-800' : 
                                                           ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                                        {{ ucfirst($task->priority) }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="mt-2 sm:flex sm:justify-between">
                                                <div class="sm:flex">
                                                    <p class="flex items-center text-sm text-gray-500">
                                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                                                            <path fill-rule="evenodd" d="M10 7a1 1 0 011 1v3a1 1 0 01-2 0V8a1 1 0 011-1z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                    </p>
                                                    <p class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM4.332 8.027c-.76.453-1.453.961-2.076 1.512.577.591 1.227 1.131 1.927 1.609C5.258 12.65 7.16 13.5 9.999 13.5c.963 0 1.9-.086 2.794-.256-1.293-1.044-2.385-2.334-3.192-3.83-.313.09-.641.136-.978.136-1.744 0-3.161-1.25-3.161-2.786 0-.556.195-1.07.53-1.495-.091.448-.137.911-.137 1.382 0 .512.053 1.011.15 1.495-.072-.115-.145-.23-.22-.346A7.997 7.997 0 014.331 8.027z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ $task->project->name }}
                                                    </p>
                                                </div>
                                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                    </svg>
                                                    <p>
                                                        Due <time datetime="{{ $task->due_date }}">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No due date' }}</time>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                
                <!-- Recent projects -->
                <div>
                    <h2 class="text-xl font-semibold mb-4">Recent Projects</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($projects as $project)
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">
                                            <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">{{ $project->name }}</a>
                                        </h3>
                                        <div class="mt-1 flex items-center">
                                            <span class="text-sm text-gray-600">
                                                {{ $project->tasks_count ?? 0 }} tasks
                                            </span>
                                            <span class="mx-2 text-gray-500">&middot;</span>
                                            <span class="text-sm text-gray-600">
                                                {{ $project->status }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                        {{ $project->completed_tasks_count ?? 0 }}/{{ $project->tasks_count ?? 0 }}
                                    </span>
                                </div>
                                
                                <div class="mt-4">
                                    <div class="relative pt-1">
                                        <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                            @php
                                                $percentage = $project->tasks_count > 0 
                                                    ? round($project->completed_tasks_count / $project->tasks_count * 100)
                                                    : 0;
                                            @endphp
                                            <div style="width: {{ $percentage }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>