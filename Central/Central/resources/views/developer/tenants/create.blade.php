<x-layouts.admin :pageTitle="'School Registration'" :role="'Developer'">
    <x-slot name="breadcrumb">
        <a href="{{ route('developer.tenants.index') }}" class="hover:text-gray-600 dark:hover:text-gray-200">Tenants</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 dark:text-gray-300">Create</span>
    </x-slot>

    <div class="mx-auto w-full max-w-6xl">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <section class="lg:col-span-4">
                <div class="rounded-3xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700 dark:text-violet-400">School Onboarding</p>
                    <h2 class="mt-3 text-4xl font-black leading-tight text-slate-900 dark:text-white">Create Your EduProfile Tenant</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-gray-300">Set up your school workspace for student profiling, status monitoring, remarks, intervention notes, and compliance document tracking.</p>

                    <div class="mt-6 space-y-3">
                        <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900/50 p-4">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Basic Plan</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-gray-300">Up to 300 students, 5 staff users, core monitoring.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900/50 p-4">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Standard Plan</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-gray-300">Up to 1,500 students, exports, and up to 20 staff users.</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-900/50 p-4">
                            <p class="text-sm font-bold text-slate-900 dark:text-white">Premium Plan</p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-gray-300">Unlimited students and users with advanced analytics.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="lg:col-span-8">
                <div class="rounded-3xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm sm:p-7">
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white">School Registration</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-gray-300">Register your school, create its admin account, and submit it for central approval.</p>

                    <form method="POST" action="{{ route('developer.tenants.store') }}" class="mt-6 space-y-5" onsubmit="return confirm('Please confirm all school details are correct before registering.');">
                        @csrf
                        @include('developer.tenants._form')

                        <div class="flex flex-col gap-3 border-t border-slate-200 dark:border-gray-700 pt-4 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('developer.tenants.index') }}" class="text-sm font-semibold text-slate-600 dark:text-gray-300 transition hover:text-cyan-700 dark:hover:text-violet-400">Already have a school account?</a>
                            <div class="flex items-center gap-3">
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 dark:bg-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-slate-900/20 dark:shadow-violet-900/20 transition hover:-translate-y-0.5 hover:bg-slate-800 dark:hover:bg-violet-500 sm:w-auto">Submit for Approval</button>
                                <a href="{{ route('developer.tenants.index') }}" class="rounded-xl border border-slate-300 dark:border-gray-600 px-4 py-3 text-sm font-semibold text-slate-600 dark:text-gray-300 transition hover:bg-slate-100 dark:hover:bg-gray-700">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-layouts.admin>
