<template>
    <app-entity-select :value="selected"
                       @input="select"
                       hide-details
                       :entities="['objekt', 'partner', 'bankkonto']"
                       append-icon=""
                       multiple
                       solo-inverted
    >
    </app-entity-select>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import Select from "../common/EntitySelect.vue"
    import {Bankkonto, Objekt, Partner} from "../../server/resources";
    import {namespace} from "vuex-class";

    const GlobalSelectModule = namespace('shared/globalSelect');

    const LegacyModule = namespace('shared/legacy');

    @Component({components: {'app-entity-select': Select}})
    export default class GlobalSelect extends Vue {
        @GlobalSelectModule.State('objekt')
        objekt: Objekt | null;

        @GlobalSelectModule.State('partner')
        partner: Partner | null;

        @GlobalSelectModule.State('bankkonto')
        bankkonto: Bankkonto | null;

        @LegacyModule.State('isLegacy')
        isLegacy: boolean;

        @GlobalSelectModule.Action('updateObjekt')
        updateObjekt: Function;

        @GlobalSelectModule.Action('updatePartner')
        updatePartner: Function;

        @GlobalSelectModule.Action('updateBankkonto')
        updateBankkonto: Function;

        dirty: boolean = false;

        select(entities) {
            if (entities instanceof Event) return;
            let partner = null;
            let objekt = null;
            let bankkonto = null;
            entities.forEach(function (entity) {
                switch (entity.constructor.type) {
                    case Partner.type:
                        partner = entity;
                        break;
                    case Objekt.type:
                        objekt = entity;
                        break;
                    case Bankkonto.type:
                        bankkonto = entity;
                }
            });
            if (this.partner !== partner) {
                this.updatePartner(partner);
                this.dirty = true;
            }
            if (this.objekt !== objekt) {
                this.updateObjekt(objekt);
                this.dirty = true;
            }
            if (this.bankkonto !== bankkonto) {
                this.updateBankkonto(bankkonto);
                this.dirty = true;
            }
            if (this.isLegacy) {
                setTimeout(() => window.location.reload(), 300);
            }
        }

        get selected() {
            let selected: Array<any> = [];
            if (this.partner) {
                selected.push(this.partner);
            }
            if (this.bankkonto) {
                selected.push(this.bankkonto);
            }
            if (this.objekt) {
                selected.push(this.objekt);
            }
            return selected;
        }
    }
</script>