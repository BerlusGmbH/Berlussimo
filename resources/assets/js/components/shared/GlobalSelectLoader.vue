<template></template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import Select from "../common/EntitySelect.vue"
    import {Bankkonto, Objekt, Partner} from "../../server/resources/models";
    import {Prop} from "vue-property-decorator";
    import {Mutation, namespace} from "vuex-class";

    const GlobalSelectMutation = namespace('shared/globalSelect', Mutation);

    @Component({components: {'app-entity-select': Select}})
    export default class GlobalSelect extends Vue {

        @GlobalSelectMutation('updateObjekt')
        updateObjekt: Function;

        @GlobalSelectMutation('updatePartner')
        updatePartner: Function;

        @GlobalSelectMutation('updateBankkonto')
        updateBankkonto: Function;

        @Prop({type: Object, default: null})
        partner: Partner | null;

        @Prop({type: Object, default: null})
        objekt: Objekt | null;

        @Prop({type: Object, default: null})
        bankkonto: Bankkonto | null;

        mounted() {
            if (this.partner) {
                Object.setPrototypeOf(this.partner, Partner.prototype);
                this.updatePartner(this.partner);
            }
            if (this.objekt) {
                Object.setPrototypeOf(this.objekt, Objekt.prototype);
                this.updateObjekt(this.objekt);
            }
            if (this.bankkonto) {
                Object.setPrototypeOf(this.bankkonto, Bankkonto.prototype);
                this.updateBankkonto(this.bankkonto);
            }
        }
    }
</script>