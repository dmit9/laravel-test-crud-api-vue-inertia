<!-- ===== Components/Telegram/StatsCard.vue ===== -->
<template>
    <div :class="[
        'bg-white rounded-lg shadow p-6 border-l-4',
        borderColors[color] || borderColors.blue
    ]">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <span class="text-2xl">{{ icon }}</span>
            </div>
            <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">{{ title }}</div>
                <div class="text-2xl font-bold text-gray-900">{{ formattedValue }}</div>
                <div v-if="change" :class="[
                    'text-sm',
                    change > 0 ? 'text-green-600' : 'text-red-600'
                ]">
                    {{ change > 0 ? '+' : '' }}{{ change }}%
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    title: string
    value: number
    icon: string
    color?: 'blue' | 'green' | 'purple' | 'orange' | 'red'
    change?: number
}

const props = withDefaults(defineProps<Props>(), {
    color: 'blue'
})

const borderColors = {
    blue: 'border-blue-500',
    green: 'border-green-500',
    purple: 'border-purple-500',
    orange: 'border-orange-500',
    red: 'border-red-500'
}

</script>
