import Echo from "laravel-echo";

export default new Echo({
    broadcaster: 'nchan',
    host: '/broadcasting/events'
});