<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head,Link,useForm,usePage } from "@inertiajs/vue3";
import {computed} from "vue";

const props = defineProps({
    user: Object,
});

const page = usePage();

const canEdit = computed  (() =>  page.props.auth.user && page.props.auth.user.id === props.user.id)

const form = useForm()

const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

console.log(csrfToken); // Проверяем вывод


const deleteUser = (id) => {
    console.log(id);
    console.log(csrfToken);
    form.delete(route('delete', id), {
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Show" />

        <div class="pt-3 sm:pt-5 lg:pt-0">

            <h2 class="text-xl font-semibold text-black dark:text-white">

                <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 sm:size-16">
                    <img :src="user.photo ? '/public/storage/' + user.photo : '/public/storage/images/default.jpg'" />
                </div>
                <div>Id: {{ user.id }}</div>
                <div>Name: {{ user.name }}</div>
                <div>Email: {{ user.email }}</div>
                <div>Phone: {{ user.phone }}</div>
                <div>position_id: {{ user.position_id }}</div>
                <div :class="{ 'opacity-50 pointer-events-none': !canEdit }" class="flex border border-indigo-600">
                    <div>
                        <Link :href="route('edit', user.id)" :class="{ 'opacity-50 pointer-events-none': !canEdit }"
                            class="px-2 py-1 text-sm bg-green-500 text-white me-2 rounded inline-block">
                        Edit
                        </Link>
                    </div>
                    <div>
                        <button type="submit" :disabled="!canEdit" @click="deleteUser(user.id)"
                            class="px-2 py-1 text-sm bg-red-500 text-white me-2 rounded inline-block">
                            Delete
                        </button>
                    </div>
                    <div v-if="!canEdit">
                        <div>
                            You can edit only your own data. Register and fill in the data for this.
                        </div>
                    </div>
                </div>
            </h2>
        </div>
    </GuestLayout>
</template>
