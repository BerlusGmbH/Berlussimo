<template>
    <app-entity-select :value="selected"
                       @input="select"
                       hide-details
                       :entities="['objekt', 'partner', 'bankkonto']"
                       append-icon=""
                       multiple class="global-select"
                       solo
                       light
                       style="background-color: #6ddfdb"
    >
    </app-entity-select>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import Select from "../common/EntitySelect.vue"
    import {Bankkonto, Objekt, Partner} from "../../server/resources/models";
    import {Action, namespace, State} from "vuex-class";

    const GlobalSelectState = namespace('shared/globalSelect', State);
    const GlobalSelectAction = namespace('shared/globalSelect', Action);

    const LegacyState = namespace('shared/legacy', State);

    @Component({components: {'app-entity-select': Select}})
    export default class GlobalSelect extends Vue {
        @GlobalSelectState('objekt')
        objekt: Objekt | null;

        @GlobalSelectState('partner')
        partner: Partner | null;

        @GlobalSelectState('bankkonto')
        bankkonto: Bankkonto | null;

        @LegacyState('isLegacy')
        isLegacy: boolean;

        @GlobalSelectAction('updateObjekt')
        updateObjekt: Function;

        @GlobalSelectAction('updatePartner')
        updatePartner: Function;

        @GlobalSelectAction('updateBankkonto')
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