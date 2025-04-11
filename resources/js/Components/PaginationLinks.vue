<script setup>
import { defineProps, defineEmits } from 'vue';

defineProps({
    paginator: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['page-change']);


const makeLabel = (label) => {
    if (label.includes("Previous")) {
        return "<<";
    } else if (label.includes("Next")) {
        return ">>";
    } else {
        return label;
    }
};


const handlePageChange = (url) => {
    if (!url) return;

    const params = new URLSearchParams(new URL(url).search);
    const page = params.get('page') || 1;

    emit('page-change', page);
};
</script>

<template>
    <div class="flex items-center rounded-md overflow-hidden shadow-lg">
        <button
            v-if="paginator.links[0].url"
            @click="handlePageChange(paginator.links[0].url)"
            class="border-x border-slate-50 w-12 h-12 grid place-items-center bg-white"
            :class="{
                'hover:bg-slate-300': paginator.links[0].url,
                'text-zinc-400': !paginator.links[0].url,
            }"
        >
            <<
        </button>

        <div v-for="(link, index) in paginator.links.slice(1, -1)" :key="link.url">
            <button
                @click="handlePageChange(link.url)"
                v-html="makeLabel(link.label)"
                class="border-x border-slate-50 w-12 h-12 grid place-items-center bg-white"
                :class="{
                    'hover:bg-slate-300': link.url,
                    'text-zinc-400': !link.url,
                    'font-bold text-blue-500': link.active,
                }"
                :disabled="!link.url"
            />
        </div>

        <button
            v-if="paginator.links[paginator.links.length - 1].url"
            @click="handlePageChange(paginator.links[paginator.links.length - 1].url)"
            class="border-x border-slate-50 w-12 h-12 grid place-items-center bg-white"
            :class="{
                'hover:bg-slate-300': paginator.links[paginator.links.length - 1].url,
                'text-zinc-400': !paginator.links[paginator.links.length - 1].url,
            }"
        >
            >>
        </button>
    </div>

    <p class="text-slate-600 text-sm">
        Showing {{ paginator.from }} to {{ paginator.to }} of {{ paginator.total }}
    </p>
</template>
