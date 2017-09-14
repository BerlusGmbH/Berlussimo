
<script lang="ts">
    import Vue from "vue";
    import Vuetify from "vuetify";
    import Component from "vue-class-component";

    Vue.use(Vuetify);

    @Component({extends: Vue.component('v-select')})
    export default class VSelect extends Vue {

        inputValue;
        selectedItems;

        genSelectedItems(val) {
            val = val || this.inputValue;
            val = [undefined, null].includes(val) ? [] : val;
            val = Array.isArray(val) ? val : [val];
            this.selectedItems = val;
        }
    }
</script>