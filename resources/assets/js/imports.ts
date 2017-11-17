import Vue from "vue";
import VueRouter from "vue-router";
import Vuex from "vuex";
import Vuetify from "vuetify";

Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(Vuetify, {
    theme: {
        primary: '#28b8b4',
        accent: '#28b8b4',
        secondary: '#424242'
    }
});

export default Vue;
