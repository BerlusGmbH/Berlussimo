<template>
    <v-chip :class="{'chip--select-multi': multiple}"
            :close="multiple"
            :key="'invoice_item-' + entity.id"
            @click.native.stop
            @input="$emit('input', $event)"
    >
        <v-avatar class="grey darken-1">
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-avatar>
        {{String(entity).trim()}}
    </v-chip>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {BankAccountStandardChart} from "../../../models";

    @Component
    export default class BankAccountStandardChartChip extends Vue {
        @Prop({type: Boolean, default: false})
        multiple;

        @Prop()
        entity: BankAccountStandardChart;
    }
</script>