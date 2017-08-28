import axios from "axios";

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token['content'];
}
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export default axios;