<script setup>
import Navbar from '@/Components/Navbar.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div>
        <Navbar />
        <slot />

    </div>
</template>
