import Vue from "vue";
import VueRouter from "vue-router";
import Vuex from "vuex";
import Vuetify from "vuetify";
import VueApollo from "vue-apollo";
import colors from "vuetify/es5/util/colors";

Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(VueApollo);
Vue.use(Vuetify, {
    theme: {
        primary: '#28b8b4',
        accent: '#909090',
        secondary: '#303030',
        info: colors.blue.lighten1,
        warning: colors.amber.darken2,
        error: colors.red.base,
        success: colors.green.lighten2
    },
});

export default Vue;
