<template>
    <app-entity-select :entities="['Property', 'Partner', 'BankAccount']"
                       :value="selected"
                       @input="select"
                       append-icon=""
                       class="global-select"
                       hide-details
                       light
                       multiple
                       solo-inverted
    >
    </app-entity-select>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import Select from "../common/EntitySelect.vue"
    import {BankAccount, Partner, Property} from "../../models";
    import GlobalSelectQuery from "./GlobalSelectQuery.graphql";
    import GlobalSelectMutation from "./GlobalSelectMutation.graphql";

    @Component({
        components: {
            'app-entity-select': Select
        },
        apollo: {
            state: {
                query: GlobalSelectQuery
            }
        }
    })
    export default class GlobalSelect extends Vue {
        state: any;

        dirty: boolean = false;

        items: Array<any> = [];

        select(entities) {
            if (entities instanceof Event) return;
            let partner: Partner | null = null;
            let property: Property | null = null;
            let bankAccount: BankAccount | null = null;
            this.dirty = false;
            for (let entity of entities) {
                switch (entity.__typename) {
                    case Partner.__typename:
                        partner = entity;
                        break;
                    case Property.__typename:
                        property = entity;
                        break;
                    case BankAccount.__typename:
                        bankAccount = entity;
                }
            }
            if (this.state.globalSelect.partner !== partner) {
                this.dirty = true;
            }
            if (this.state.globalSelect.property !== property) {
                this.dirty = true;
            }
            if (this.state.globalSelect.bankAccount !== bankAccount) {
                this.dirty = true;
            }
            if (this.dirty) {
                this.$apollo.mutate({
                    mutation: GlobalSelectMutation,
                    variables: {
                        partnerId: partner ? partner.id : null,
                        propertyId: property ? property.id : null,
                        bankAccountId: bankAccount ? bankAccount.id : null
                    }
                }).then(() => {
                    if (this.state.isLegacy) {
                        setTimeout(() => window.location.reload(), 300);
                    }
                });
            }
        }

        get selected() {
            let selected: any[] = [];
            if (this.state.globalSelect.partner) {
                selected.push(this.state.globalSelect.partner);
            }
            if (this.state.globalSelect.bankAccount) {
                selected.push(this.state.globalSelect.bankAccount);
            }
            if (this.state.globalSelect.property) {
                selected.push(this.state.globalSelect.property);
            }
            return selected;
        }
    }
</script>