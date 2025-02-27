<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import PaginationLinks from '@/Components/PaginationLinks.vue'
import { Head } from '@inertiajs/vue3';

defineProps({
    users: Object,
    sortField: String,
    sortDirection: String
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
};
</script>

<template>
    <FrontendLayout>

        <Head title="Home" />
        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">

            <div
                class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
                <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">


                    <main class="mt-6">
                        <div class="mb-3 flex gap-2 border ml-1 rounded p-2 bg-gray-100">

                            <div class="">
                                <div class="col-lg-6 col-6 border border-secondary ml-1 rounded  p-1">
                                    <h2>Available links</h2>
                                    <li><a href="/api/v1/users">/api/v1/users</a></li>
                                    <li><a href="/api/v1/positions">/api/v1/positions</a></li>
                                    <li><a href="/api/v1/users/5">/api/v1/users/5</a></li>
                                </div>
                            </div>

                        </div>
                        <div class="mb-3 flex gap-2 border ml-1 rounded p-2 bg-gray-100">
                            <p class="font-semibold">Sort By:</p>

                            <a :href="route('home', { sort: 'id', direction: sortField === 'id' && sortDirection === 'asc' ? 'desc' : 'asc' })"
                                :class="['px-3 py-1 rounded text-white', sortField === 'id' ? (sortDirection === 'asc' ? 'bg-blue-600' : 'bg-blue-400') : 'bg-blue-500']">
                                ID {{ sortField === 'id' ? (sortDirection === 'asc' ? '⬆️' : '⬇️') : '' }}
                            </a>

                            <a :href="route('home', { sort: 'name', direction: sortField === 'name' && sortDirection === 'asc' ? 'desc' : 'asc' })"
                                :class="['px-3 py-1 rounded text-white', sortField === 'name' ? (sortDirection === 'asc' ? 'bg-green-600' : 'bg-green-400') : 'bg-green-500']">
                                Name {{ sortField === 'name' ? (sortDirection === 'asc' ? '⬆️' : '⬇️') : '' }}
                            </a>

                            <a :href="route('home', { sort: 'created_at', direction: sortField === 'created_at' && sortDirection === 'asc' ? 'desc' : 'asc' })"
                                :class="['px-3 py-1 rounded text-white', sortField === 'created_at' ? (sortDirection === 'asc' ? 'bg-indigo-600' : 'bg-indigo-400') : 'bg-indigo-500']">
                                Date {{ sortField === 'created_at' ? (sortDirection === 'asc' ? '⬆️' : '⬇️') : '' }}
                            </a>
                        </div>

                        <div class="flex flex-wrap gap-2 justify-center">
                            <div v-for="user in users.data" :key="user.id" class="flex w-full lg:w-2/5">

                                <a :href="route('show', user.id)" id="docs-card"
                                    class=" w-full lg:p-3 lg:pb-2 flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-2 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20]  dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                                    <div class="relative w-full justify-between flex items-center gap-2 lg:items-end">
                                        <div id="docs-card-content" class="flex w-full items-start gap-2 lg:flex-col">
                                            <div
                                                class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                                                <img
                                                    :src="user.photo ? '/storage/' + user.photo : '/storage/images/default.jpg'" />
                                            </div>

                                            <div class="pt-3 sm:pt-5 lg:pt-0">
                                                <h2 class="text-xl font-semibold text-black dark:text-white">
                                                    <div>Id: {{ user.id }}</div>
                                                    <div>Name: {{ user.name }}</div>
                                                    <div>Email: {{ user.email }}</div>
                                                    <div>Phone: {{ user.phone }}</div>
                                                    <div>Position: {{ user.position.name }}</div>
                                                    <div>Register: {{ formatDate(user.updated_at) }}</div>
                                                </h2>
                                            </div>
                                        </div>
                                        <svg class="size-6 shrink-0 stroke-[#FF2D20]" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                                        </svg>
                                    </div>
                                </a>
                            </div>

                        </div>

                    </main>
                    <div class="">
                        <PaginationLinks :paginator="users" />
                    </div>

                    <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    </footer>
                </div>
            </div>
        </div>
    </FrontendLayout>
</template>
