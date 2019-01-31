<template>
    <v-chip :class="{'chip--select-multi': multiple}"
            :close="multiple"
            :key="'invoiceItem-' + entity.id"
            @click.native.stop
            @input="$emit('input', $event)"
    >
        <v-avatar class="grey darken-1">
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-avatar>
        {{entity.itemNumber.trim()}}:
        {{String(entity).trim()}}
    </v-chip>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {InvoiceItem} from "../../../models";

    @Component
    export default class InvoiceItemChip extends Vue {
        @Prop({type: Boolean, default: false})
        multiple;

        @Prop()
        entity: InvoiceItem;
    }
</script>