<template>
    <FrontendLayout>

        <Head title="Telegram bot" />
        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
            <div
                class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
                <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                    <main class="mt-6">
                        <div class="mt-4 p-2">
                            <div class=" text-3xl font-bold">Telegram task management.</div>
                            <div class="text-black">
                                <p>You can go to bot @prototypecodetest_bot, create a task there and it will be
                                    displayed
                                    here.</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 justify-center">
                            <div v-for="task in tasks" :key="task.id" class="flex w-full lg:w-2/5">
                                <div
                                    class=" w-full lg:p-3 lg:pb-2 flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-2 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20]  dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                                    <div class="relative w-full justify-between flex items-center gap-2 lg:items-end">
                                        <div id="docs-card-content" class="flex w-full items-start gap-2 lg:flex-col">
                                            <div class="pt-3 sm:pt-5 lg:pt-0">
                                                <h2 class="text-xl font-semibold text-black dark:text-white">
                                                    <div>Task_id: {{ task.id }}</div>
                                                    <div>User_id: {{ task.user_id }}</div>
                                                    <div>Title: {{ task.title }}</div>
                                                    <div>Description: {{ task.description }}</div>
                                                    <div>Status: {{ task.status }}</div>
                                                    <div>created_at: {{ formatDate(task.created_at) }}</div>
                                                    <div>updated_at: {{ formatDate(task.updated_at) }}</div>
                                                </h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </FrontendLayout>
</template>
<script setup lang="ts">
import FrontendLayout from "@/Layouts/FrontendLayout.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    tasks: Object
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
