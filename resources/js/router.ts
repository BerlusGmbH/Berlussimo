import VueRouter from "vue-router";
import routes from "./routes";
import qs from "qs";

const router = new VueRouter({
    mode: 'history',
    routes,
    parseQuery: (query: any): object => {
        return qs.parse(query);
    },
    stringifyQuery(query: any): string {
        let result = qs.stringify(query, {encode: false});

        return result ? ('?' + result) : '';
    }
});

export default router;