<template>
    <app-entity-select :value="selected" @change="select" hide-details :entities="['partner', 'bankkonto', 'objekt']"
                       append-icon="" multiple class="global-select" @focusout.native="checkReload"
                       @keydown.native.esc="checkReload" @chip-close="checkReload">
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
            let partner = null;
            let objekt = null;
            let bankkonto = null;
            entities.forEach(function (entity) {
                switch (entity.constructor.name) {
                    case "Partner":
                        partner = entity;
                        break;
                    case "Objekt":
                        objekt = entity;
                        break;
                    case "Bankkonto":
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

        checkReload(event) {
            if (this.isLegacy && this.dirty && (event.target.tagName === 'A' || event.target.tagName === 'INPUT')) {
                window.location.reload();
            }
        }
    }
</script>

<style>
    .global-select .input-group__details::after {
        background-color: #ffffff;
    }

    .global-select.input-group.input-group--focused .input-group__input .icon {
        color: #ffffff;
    }

    .global-select.input-group--text-field input {
        caret-color: #ffffff;
    }

    .global-select i {
        margin-right: 0;
    }
</style>