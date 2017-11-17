<script lang="ts">
    import Vue from "../../imports";
    import Vuetify from "vuetify";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";

    @Component({extends: Vue.component('v-select')})
    export default class VSelect extends Vue {
        @Prop({type: [String, Number]})
        menuZIndex;
    }
</script>