import axios from "axios";
import store from "../store";

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token['content'];
}
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.interceptors.response.use(function (response) {
    return response;
}, function (error) {
    if (401 === error.response.status) {
        store.dispatch('auth/appLogout');
    }
    return Promise.reject(error);
});

axios.interceptors.request.use(function (config) {
    if (store.state['auth']['csrf']) {
        config.headers['X-CSRF-TOKEN'] = store.state['auth']['csrf'];
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});

export default axios;