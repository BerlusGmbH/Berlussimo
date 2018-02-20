<template>
    <v-chip
            :close="multiple"
            @input="$emit('input', $event)"
            @click.native.stop
            :class="{'chip--select-multi': multiple}"
            :key="'purchase_contract-' + entity.getID()"
    >
        <v-avatar class="grey darken-1">
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-avatar>
        {{String(entity)}}
    </v-chip>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class PurchaseContractChip extends Vue {
        @Prop({type: Boolean, default: false})
        multiple;

        @Prop()
        entity;
    }
</script>