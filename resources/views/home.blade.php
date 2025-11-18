<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
@php
    $years = range(2023, 2027);
    $financialYears = [];
    foreach ($years as $y) {
        $financialYears[] = $y . '-' . sprintf('%02d', ($y+1)%100);
    }
    $months = ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'];
@endphp
        <!-- Nav Bar -->
        <nav class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Doc Generator</h1>
                    </div>
                    @if (Route::has('login'))
                        <div class="flex items-center space-x-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Toast Alerts -->
            <div id="toast-container" class="fixed top-6 right-6 z-50 flex flex-col space-y-3 items-end">
                @if(session('success'))
                    <div class="toast-alert px-4 py-3 rounded shadow bg-green-100 text-green-800 border border-green-300 min-w-[220px]">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="toast-alert px-4 py-3 rounded shadow bg-red-100 text-red-800 border border-red-300 min-w-[220px]">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('warning'))
                    <div class="toast-alert px-4 py-3 rounded shadow bg-yellow-100 text-yellow-800 border border-yellow-300 min-w-[220px]">
                        {{ session('warning') }}
                    </div>
                @endif
                @if(session('info'))
                    <div class="toast-alert px-4 py-3 rounded shadow bg-blue-100 text-blue-800 border border-blue-300 min-w-[220px]">
                        {{ session('info') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="toast-alert px-4 py-3 rounded shadow bg-red-100 text-red-800 border border-red-300 min-w-[220px]">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                    Generate Your Documents
                </h2>
                <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 dark:text-gray-400 sm:mt-4">
                    Upload your files and let us handle the document generation for you
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 mt-12">
                <!-- BG Upload Card -->
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="absolute top-0 right-0 p-4">
                            <div class="h-8 w-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">BG File</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Upload your BG excel file</p>
                            <form action="{{ route('reports.upload.bg') }}" method="POST" enctype="multipart/form-data" class="block">
                                @csrf

                                <!-- financial year select -->
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    Financial Year
                                    <select name="financial_year" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                        <option value="">Select year</option>
                                        @foreach($financialYears as $fy)
                                            <option value="{{ $fy }}" {{ old('financial_year')==$fy ? 'selected' : '' }}>{{ $fy }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                @error('financial_year')
                                    <div class="text-xs text-red-600 mb-2">{{ $message }}</div>
                                @enderror

                                <!-- file input -->
                                <label class="relative group cursor-pointer block">
                                    <input type="file" name="bg_file" accept=".xlsx,.xls" class="hidden" id="bg-file"/>
                                    <div class="flex items-center justify-center px-6 py-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg group-hover:border-green-500 dark:group-hover:border-green-400 transition-colors">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-green-500 dark:group-hover:text-green-400">
                                            Choose file
                                        </span>
                                    </div>
                                </label>

                                <div class="mt-3 text-sm text-gray-700 dark:text-gray-300" id="bg-file-name">
                                    @if(session('bg_filename'))
                                        Selected: {{ session('bg_filename') }}
                                    @else
                                        No file chosen
                                    @endif
                                </div>

                                @error('bg_file')
                                    <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                                @enderror

                                <button type="submit" class="mt-4 w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                                    Upload
                                </button>

                                @if(session('bg_status'))
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ session('bg_status') }}</div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <!-- RAR Upload Card -->
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="absolute top-0 right-0 p-4">
                            <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">RAR File</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Upload your RAR excel file</p>
                            <form action="{{ route('reports.upload.rar') }}" method="POST" enctype="multipart/form-data" class="block">
                                @csrf

                                <!-- financial year select -->
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    Financial Year
                                    <select name="financial_year" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                        <option value="">Select year</option>
                                        @foreach($financialYears as $fy)
                                            <option value="{{ $fy }}" {{ old('financial_year')==$fy ? 'selected' : '' }}>{{ $fy }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                @error('financial_year')
                                    <div class="text-xs text-red-600 mb-2">{{ $message }}</div>
                                @enderror

                                <!-- month select -->
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    Month
                                    <select name="month" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                        <option value="">Select month</option>
                                        @foreach($months as $m)
                                            <option value="{{ $m }}" {{ old('month')==$m ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                @error('month')
                                    <div class="text-xs text-red-600 mb-2">{{ $message }}</div>
                                @enderror

                                <!-- file input -->
                                <label class="relative group cursor-pointer block">
                                    <input type="file" name="rar_file" accept=".xlsx,.xls" class="hidden" id="rar-file"/>
                                    <div class="flex items-center justify-center px-6 py-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg group-hover:border-blue-500 dark:group-hover:border-blue-400 transition-colors">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-400">
                                            Choose file
                                        </span>
                                    </div>
                                </label>

                                <div class="mt-3 text-sm text-gray-700 dark:text-gray-300" id="rar-file-name">
                                    @if(session('rar_filename'))
                                        Selected: {{ session('rar_filename') }}
                                    @else
                                        No file chosen
                                    @endif
                                </div>

                                @error('rar_file')
                                    <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                                @enderror

                                <button type="submit" class="mt-4 w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                    Upload
                                </button>

                                @if(session('rar_status'))
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ session('rar_status') }}</div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Revenue Schedule Upload Card -->
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="absolute top-0 right-0 p-4">
                            <div class="h-8 w-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Revenue Schedule</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-3">Upload your revenue schedule HTML file</p>
                            <form action="{{ url('/upload-revenue') }}" method="POST" enctype="multipart/form-data" class="block">
                                @csrf

                                <!-- year select (plain year values) -->
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    Year
                                    <select name="year" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                        <option value="">Select year</option>
                                        @foreach($years as $y)
                                            <option value="{{ $y }}" {{ old('year')==$y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                @error('year')
                                    <div class="text-xs text-red-600 mb-2">{{ $message }}</div>
                                @enderror

                                <!-- month select -->
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    Month
                                    <select name="month" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                        <option value="">Select month</option>
                                        @foreach($months as $m)
                                            <option value="{{ $m }}" {{ old('month')==$m ? 'selected' : '' }}>{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                @error('month')
                                    <div class="text-xs text-red-600 mb-2">{{ $message }}</div>
                                @enderror

                                <!-- file input -->
                                <label class="relative group cursor-pointer block">
                                    <input type="file" name="revenue_file" accept=".html,.htm" class="hidden" id="revenue-file"/>
                                    <div class="flex items-center justify-center px-6 py-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg group-hover:border-purple-500 dark:group-hover:border-purple-400 transition-colors">
                                        <span class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-purple-500 dark:group-hover:text-purple-400">
                                            Choose file
                                        </span>
                                    </div>
                                </label>

                                <div class="mt-3 text-sm text-gray-700 dark:text-gray-300" id="revenue-file-name">
                                    @if(session('revenue_filename'))
                                        Selected: {{ session('revenue_filename') }}
                                    @else
                                        No file chosen
                                    @endif
                                </div>

                                @error('revenue_file')
                                    <div class="mt-2 text-xs text-red-600">{{ $message }}</div>
                                @enderror

                                <button type="submit" class="mt-4 w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700">
                                    Upload
                                </button>

                                @if(session('revenue_status'))
                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ session('revenue_status') }}</div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
             </div>
 
             <!-- Generate Document Card -->
             <div class="mt-12 max-w-md mx-auto">
                 <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                     <div class="p-6">
                         <div class="absolute top-0 right-0 p-4">
                             <div class="h-8 w-8 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                 <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                 </svg>
                             </div>
                         </div>
                         <div class="mt-8">
                             <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Generate Document</h3>
                             <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">Select period and generate your document</p>

                             <form action={{ route('reports.download') }} method="POST" class="block">
                                 @csrf
                                 <!-- year select -->
                                 <label class="block text-sm text-gray-700 dark:text-gray-300 mb-2">
                                     Year
                                     <select name="year" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                         <option value="">Select year</option>
                                         @foreach($years as $y)
                                             <option value="{{ $y }}" {{ old('year')==$y ? 'selected' : '' }}>{{ $y }}</option>
                                         @endforeach
                                     </select>
                                 </label>
                                 @error('year')
                                     <div class="text-xs text-red-600 mb-2">{{ $message }}</div>
                                 @enderror

                                 <!-- month select -->
                                 <label class="block text-sm text-gray-700 dark:text-gray-300 mb-2">
                                     Month
                                     <select name="month" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm">
                                         <option value="">Select month</option>
                                         @foreach($months as $m)
                                             <option value="{{ $m }}" {{ old('month')==$m ? 'selected' : '' }}>{{ $m }}</option>
                                         @endforeach
                                     </select>
                                 </label>
                                 @error('month')
                                     <div class="text-xs text-red-600 mb-2">{{ $message }}</div>
                                 @enderror

                                 <button type="submit" class="mt-6 w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                     Generate Document
                                     <svg class="ml-2 -mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                     </svg>
                                 </button>
                             </form>
                         </div>
                     </div>
                 </div>
             </div>
         </main>
 
        <!-- update selected filename on file input change -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const map = [
                    { inputId: 'rar-file', nameId: 'rar-file-name' },
                    { inputId: 'bg-file', nameId: 'bg-file-name' },
                    { inputId: 'revenue-file', nameId: 'revenue-file-name' },
                ];

                map.forEach(({ inputId, nameId }) => {
                    const input = document.getElementById(inputId);
                    const nameEl = document.getElementById(nameId);
                    if (!input || !nameEl) return;

                    input.addEventListener('change', () => {
                        const f = input.files && input.files[0];
                        nameEl.textContent = f ? 'Selected: ' + f.name : 'No file chosen';
                    });
                });
           
            });
        </script>

        <!-- Toast auto-hide script -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Toast auto-hide
                setTimeout(function () {
                    document.querySelectorAll('.toast-alert').forEach(function(el) {
                        el.style.transition = 'opacity 0.5s';
                        el.style.opacity = '0';
                        setTimeout(function() { el.remove(); }, 600);
                    });
                }, 4000);
            });
        </script>
 
     </body>
 </html>
