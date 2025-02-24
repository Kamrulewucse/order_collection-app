import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '50896a7437bc375750f0',
    cluster: 'mt1',
    forceTLS: true
});

Echo.channel('location-channel')
    .listen('LocationUpdated', (event) => {
        console.log(event);
    });