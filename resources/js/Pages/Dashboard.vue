<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div  class=" mx-auto min-h-screen  bg-gray-100 pt-6 sm:justify-center sm:pt-0" >

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        You're logged in, {{ $page.props.auth.user.name }}!
                    </div>
                    <div class="pt-3 sm:pt-5 lg:pt-0">


                        <h2 class="text-xl font-semibold text-black dark:text-white">
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                <img
                                    :src="$page.props.auth.user.photo ? '/public/storage/' + $page.props.auth.user.photo : '/public/storage/images/default.jpg'" />
                            </div>
                            <div>Id: {{ $page.props.auth.user.id }}</div>
                            <div>Name: {{ $page.props.auth.user.name }}</div>
                            <div>Email: {{ $page.props.auth.user.email }}</div>
                            <div>Phone: {{ $page.props.auth.user.phone }}</div>

                        </h2>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </AuthenticatedLayout>
</template>
