<template>
    <div style="display: inherit; width: 100%; height: 100%">
        <v-list-tile-avatar>
            <v-icon>{{entity.getEntityIcon()}}</v-icon>
        </v-list-tile-avatar>
        <v-list-tile-content>
            <v-list-tile-title>{{String(entity)}}
            </v-list-tile-title>
            <v-list-tile-sub-title>{{entity.KONTO}}</v-list-tile-sub-title>
        </v-list-tile-content>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class BookingAccountTile extends Vue {
        @Prop()
        entity;
    }
</script>