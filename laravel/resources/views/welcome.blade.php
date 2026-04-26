<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Assessment') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <style>
            *,:before,:after{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e3e3e0}
            :before,:after{--tw-content:""}
            html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:"Instrument Sans",ui-sans-serif,system-ui,sans-serif}
            body{margin:0;line-height:inherit;background-color:#FDFDFC}
            a{color:inherit;text-decoration:inherit}
            h1{font-size:inherit;font-weight:inherit}
            .flex{display:flex}
            .min-h-screen{min-height:100vh}
            .flex-col{flex-direction:column}
            .items-center{align-items:center}
            .justify-center{justify-content:center}
            .px-6{padding-left:1.5rem;padding-right:1.5rem}
            .py-12{padding-top:3rem;padding-bottom:3rem}
            .text-center{text-align:center}
            .text-3xl{font-size:1.875rem;line-height:2.25rem}
            .text-lg{font-size:1.125rem;line-height:1.75rem}
            .text-sm{font-size:0.875rem;line-height:1.25rem}
            .text-xs{font-size:0.75rem;line-height:1rem}
            .font-semibold{font-weight:600}
            .font-medium{font-weight:500}
            .text-amber-600{color:#d97706}
            .text-gray-600{color:#4b5563}
            .text-gray-500{color:#6b7280}
            .mt-2{margin-top:0.5rem}
            .mt-4{margin-top:1rem}
            .mt-6{margin-top:1.5rem}
            .mt-8{margin-top:2rem}
            .mb-2{margin-bottom:0.5rem}
            .max-w-md{max-width:28rem}
            .bg-white{background-color:#ffffff}
            .bg-amber-600{background-color:#d97706}
            .text-white{color:#ffffff}
            .px-8{padding-left:2rem;padding-right:2rem}
            .py-3{padding-top:0.75rem;padding-bottom:0.75rem}
            .inline-block{display:inline-block}
            .rounded{border-radius:0.25rem}
            .transition-colors{transition-property:color,background-color,border-color;transition-timing-function:cubic-bezier(0.4,0,0.2,1);transition-duration:150ms}
            .hover\:bg-amber-700:hover{background-color:#b45309}
            @media (prefers-color-scheme: dark) {
                .dark\:bg-gray-900{background-color:#111827}
                .dark\:text-gray-300{color:#d1d5db}
            }
        </style>
    </head>
    <body class="flex min-h-screen flex-col items-center justify-center bg-white dark:bg-gray-900">
        <div class="max-w-md px-6 text-center">
            <h1 class="text-3xl font-semibold text-amber-600">
                {{ config('app.name', 'Assessment') }}
            </h1>
            <p class="mt-2 text-lg font-medium text-gray-600 dark:text-gray-300">
                User Management System
            </p>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-300">
                A production-grade admin panel for managing user records with
                asynchronous activity logging.
            </p>

            <div class="mt-8">
                <a
                    href="/admin/login"
                    class="inline-block rounded bg-amber-600 px-8 py-3 text-white transition-colors hover:bg-amber-700"
                >
                    Admin Login
                </a>
            </div>

            <p class="mt-6 text-xs text-gray-500 dark:text-gray-300">
                Powered by Laravel 13, Filament 5, PostgreSQL & MongoDB
            </p>
        </div>
    </body>
</html>
