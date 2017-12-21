<template>
    <v-chip
            :close="multiple"
            @input="$emit('input', $event)"
            @click.native.stop
            :class="{'chip--select-multi': multiple}"
            :key="'bank_account-' + entity.KONTENRAHMEN_KONTO_ID"
    >
        <v-avatar class="grey darken-1">
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-avatar>
        <template v-if="String(entity)">
            {{entity.KONTO}}: {{String(entity)}}
        </template>
        <template v-else>
            {{entity.KONTO}}
        </template>
    </v-chip>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class BookingAccountChip extends Vue {
        @Prop({type: Boolean, default: false})
        multiple;

        @Prop()
        entity;
    }
</script>