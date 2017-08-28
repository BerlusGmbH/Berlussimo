<script lang="ts">
    import Vue from "vue";
    import Vuetify from "vuetify";
    import Component from "vue-class-component";
    import {Watch} from "vue-property-decorator";

    Vue.use(Vuetify);

    @Component({extends: Vue.component('VSelect')})
    export default class VSelect extends Vue {

        items;
        inputValue;

        filterSearch() {
            return this.items.slice();
        }

        @Watch('searchValue')
        onSearchValueChange(newValue) {
            (this.$refs.menu as any).listIndex = -1;
            this.$emit('search', newValue);
        }

        get selectedItems() {
            if (this.inputValue === null ||
                    typeof this.inputValue === 'undefined') return [];
            if (this.inputValue instanceof Array) {
                return this.inputValue;
            } else {
                return [this.inputValue];
            }
        }
    }
</script>