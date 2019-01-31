<template>
    <v-chip
            :class="{'chip--select-multi': multiple}"
            :close="multiple"
            :key="'booking_account-' + entity.id"
            @click.native.stop
            @input="$emit('input', $event)"
    >
        <v-avatar class="grey darken-1">
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-avatar>
        <template v-if="String(entity)">
            {{entity.number}}: {{String(entity)}}
        </template>
        <template v-else>
            {{entity.number}}
        </template>
    </v-chip>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {BookingAccount} from "../../../models";

    @Component
    export default class BookingAccountChip extends Vue {
        @Prop({type: Boolean, default: false})
        multiple;

        @Prop()
        entity: BookingAccount;
    }
</script>
