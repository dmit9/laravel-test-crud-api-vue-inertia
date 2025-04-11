import axios from 'axios';

//axios.defaults.baseURL = window.location.origin;
axios.defaults.baseURL = 'https://inertia.prototypecodetest.site';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
