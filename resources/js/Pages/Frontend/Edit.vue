<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';


const props = defineProps({
    positions: Array,
    user: Object
})

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone,
    position_id: props.user.position_id,
    photo: null,
});

const change = (e) => {
    form.photo = e.target.files[0];
}

const submit = () => {
    const data = { ...form };

    if (!form.photo || typeof form.photo === 'string') {
        delete data.photo; // Удаляем поле, если файл не менялся
    }

    form.post(route('update', props.user.id), {
        data,
        forceFormData: true
    });
};
</script>

<template>
    <Head title="Dashboard" />
    <GuestLayout>
        <form @submit.prevent="submit">
            <div class="mt-4">
                <img :src="props.user.photo ? '/storage/' + props.user.photo : '/storage/images/default.jpg'" />
                <InputLabel for="photo" value="Upload" />
                <input type="file" @input="change" id="photo">
                <InputError class="mt-2" :message="form.errors.photo" />
            </div>
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus
                    autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                    autocomplete="email" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="phone" value="Phone" />
                <TextInput id="phone" type="phone" class="mt-1 block w-full" v-model="form.phone" required
                    autocomplete="phone" />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="mt-4">
                <label for="position">Choose position </label>
                <select v-model="form.position_id" class="w-full" name="position_id" id="position_id">
                    <option v-for="position in positions" :key="position.id" :value="position.id">
                        id: {{ position.id }} name: {{ position.name }}
                    </option>
                </select>
            </div>

            <div class="mt-4 flex items-center justify-center">
                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Update
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
