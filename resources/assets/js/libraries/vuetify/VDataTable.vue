<script lang="ts">
    import Vue from "../../imports";
    import Vuetify from "vuetify";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";

    Vue.use(Vuetify);

    @Component({extends: Vue.component('VDataTable')})
    export default class VDataTable extends Vue {
        @Prop({type: Boolean})
        showExpands;

        expanded: any;
        items: Array<any>;
        itemKey: string;

        @Watch('showExpands')
        onShowExpandsChange(val) {
            this.items.forEach((v) => {
                this.$set(this.expanded, v[this.itemKey], val);
            });
        }
    }
</script>