<style scoped>
canvas {
    max-height: 300px;
}
</style>

<script setup>
import FrontendLayout from '@/Layouts/FrontendLayout.vue';
import {Line} from 'vue-chartjs'
import {Head, router} from '@inertiajs/vue3';
import {computed, ref, watch, onMounted} from "vue";
import {debounce} from 'lodash'
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

const props = defineProps({
    data: {type: Object},
    list: {type: Array},
    city: {type: Object},
    filters: {type: Object} 
});

const filters = ref(JSON.parse(localStorage.getItem('filters')) || { ...props.filters });

const langs = ['ru', 'en', 'de', 'it', 'fr'];
const hoursCount = ['5', '10', '20', '30', '40'];
const cityes = ['Kharkiv', 'London', 'Moscow', 'Berlin', 'Rome', 'Paris'];

onMounted(() => {
    if (localStorage.getItem('filters')) {
        router.get('/weather', { ...filters.value }, { preserveState: true });
    }
});
//const filters = ref({...props.filters});

watch(filters, debounce((newFilters) => {
    localStorage.setItem('filters', JSON.stringify(newFilters));
    router.get('/weather', { ...newFilters }, { preserveState: true });
}, 1000), { deep: true });


const formatDate = (timestamp, timezoneOffset = 0) => {
    const date = new Date((timestamp + timezoneOffset) * 1000);
    return date.toLocaleString('en-GB', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    });
};

const formatDateTime2 = (dateTime) => {
    const date = new Date(dateTime);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Месяцы с 0 начинаются
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}-${month} ${hours}:${minutes}`;
};

const options = {
    responsive: true,
    maintainAspectRatio: false
}

const chartData = computed(() => {
    if (!props.list || !props.list.length) {
        console.warn('list пустой или не определён')
        return {
            labels: [],
            datasets: []
        }
    }
    return {
        labels: props.list.map(item => formatDateTime2(item.dt_txt)),
        datasets: [
            {
                label: props.city.name + ' °C',
                //   backgroundColor: '#f87979',
                borderColor: '#f87979',
                data: props.list.map(item => item.main?.temp) 
            }
        ]
    }
})
</script>

<template>
    <FrontendLayout>
        <Head title="Weather"/>
        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
            <div class="relative flex p-3 gap-3 min-h-screen flex-col items-center justify-center ">
                <div class="w-full lg:p-3 lg:pb-2 flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-2 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20]  dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                    <div class="mt-4 text-3xl font-bold p-2">Openweather {{ city.name }}</div>
                    <div class="flex p-2 gap-2 flex-wrap justify-center items-center ">
                        <label for="position">Choose languare </label>
                        <div class="col-lg-6 col-6 border border-secondary ml-1 rounded  p-1 text-black">
                            <select v-model="filters.languare" class="w-full cursor-pointer" name="languare" >
                                <option v-for="languare in langs" :key="index" :value="languare"  >
                                    {{ languare }}
                                </option>
                            </select>
                        </div>
                        <label for="position">Choose Hours count </label>
                        <div class="col-lg-6 col-6 border border-secondary ml-1 rounded  p-1 text-black">
                            <select v-model="filters.hours" class="w-full cursor-pointer" name="hours">
                                <option v-for="hours in hoursCount" :key="index" :value="hours">
                                    {{ hours }}
                                </option>
                            </select>
                        </div>
                        <label for="position">Choose City </label>
                        <div class="col-lg-6 col-6 border border-secondary ml-1 rounded  p-1 text-black">
                            <select v-model="filters.citys" class="w-full cursor-pointer" name="citys">
                                <option v-for="citys in cityes" :key="index" :value="citys">
                                    {{ citys }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <Line :data="chartData" :options="options" class="p-2"/>
                </div>

                <div class="w-full  lg:p-3 lg:pb-2 flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-2 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20]  dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                    <div>City population: {{ city.population }}</div>
                    <div>Sunrise: {{ formatDate(city.sunrise, city.timezone) }} Sunset: {{
                            formatDate(city.sunset,
                                city.timezone)
                        }}
                    </div>
                    <table class="table-auto w-full">
                        <tbody class="  p-2  font-semibold text-black dark:text-white">
                        <tr v-for="(i, index) in list" :key="index">
                            <th>{{ formatDateTime2(i.dt_txt) }}</th>
                            <th>
                                <img :src="`https://openweathermap.org/img/wn/${i.weather[0].icon}@2x.png`"
                                     :alt="i.weather[0].description" class="h-10 w-10"/>
                            </th>
                            <th>
                                <div v-for="(y, index) in i.weather" :key="index">{{ y.description }}</div>
                            </th>
                            <th>
                                <div>humidity: {{ i.main.humidity }}%</div>
                            </th>
                            <th>
                                <div>wind: {{ i.wind.speed }}</div>
                            </th>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </FrontendLayout>
</template>
