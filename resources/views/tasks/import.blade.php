<div class="mt-8">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Import Tasks
            </h3>
            <div class="mt-2 max-w-xl text-sm text-gray-500">
                <p>Upload a CSV or Excel file to import multiple tasks at once.</p>
            </div>
            <form action="{{ route('tasks.import') }}" method="POST" enctype="multipart/form-data" class="mt-5">
                @csrf
                <div class="flex items-center">
                    <label for="file" class="sr-only">Choose file</label>
                    <input type="file" name="file" id="file" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-indigo-50 file:text-indigo-700
                        hover:file:bg-indigo-100
                    ">
                    <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Import
                    </button>
                </div>
            </form>
            <div class="mt-3">
                <a href="{{ route('tasks.export') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Export All Tasks
                </a>
                @if(request()->route('project'))
                <a href="{{ route('tasks.export', ['project_id' => request()->route('project')->id]) }}" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Export Project Tasks
                </a>
                @endif
            </div>
        </div>
    </div>
</div>