<script setup>
import {computed} from 'vue'

const props = defineProps({
    field: String,
    label: String,
    activeField: String,
    direction: String,
    baseColor: String
})

const emit = defineEmits(['sort'])

const isActive = computed(() => props.activeField === props.field)
const nextDirection = computed(() =>
    isActive.value && props.direction === 'asc' ? 'desc' : 'asc'
)

const classes = computed(() =>
    isActive.value ? props.direction === 'asc' ? ` bg-${props.baseColor}-600` : ` bg-${props.baseColor}-400` : ` bg-${props.baseColor}-500`
)

const handleClick = (e) => {
    e.preventDefault()
    emit('sort', {field: props.field, direction: nextDirection.value})
}
</script>

<template>
    <button
        type="button"
        :class="classes + ' px-3 py-1 rounded text-white'"
        @click="handleClick"
    >
        {{ label }} {{ isActive ? (direction === 'asc' ? '⬆️' : '⬇️') : '' }}
    </button>
</template>
