<script lang="ts">
    import Vue from "vue";
    import Vuetify from "vuetify";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";

    Vue.use(Vuetify);

    @Component({extends: Vue.component('v-select')})
    export default class VSelect extends Vue {
        @Prop({type: [String, Number]})
        menuZIndex;
    }
</script>