<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <nav class="flex justify-between p-4 bg-gray-200">
        <div class="text-lg">
            <div v-if="$page.props.auth.user">Name: {{ $page.props.auth.user.name }},
            ID:  {{$page.props.auth.user.id }}
        </div>
        </div>
        <div>
            <ul class="flex gap-x-4 me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <Link class="text-dark" :href="route('home')">Home
                    </Link>
                </li>
                <li class="nav-item">
                    <Link class="text-dark" :href="route('weather')">Weather API
                    </Link>
                </li>

                <li v-if="!$page.props.auth.user" class="nav-item">
                    <Link class="text-dark" :href="route('login')">Login
                    </Link>
                </li>
                <li v-if="!$page.props.auth.user" class="nav-item">
                    <Link class="text-dark" :href="route('register')">Register
                    </Link>
                </li>
                <li  v-if="$page.props.auth.user">
                    <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    >
                    Log Out
                    </Link>
                </li>
            </ul>
        </div>
    </nav>
</template>
