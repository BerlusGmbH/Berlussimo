<template>
    <v-dialog :value="value" @input="$emit('input', $event)" lazy width="480">
        <v-card>
            <v-card-title class="headline">Wollen Sie dieses Detail entfernen?</v-card-title>
            <v-card-text>
                <app-identifier :value="detail"></app-identifier>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn flat="flat" @click.native="$emit('input', false)">Abbrechen</v-btn>
                <v-btn class="red" @click.native="$emit('delete'); $emit('input', false)">Entfernen</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Detail} from "../../../server/resources/models";

    @Component
    export default class DetailDeleteDialog extends Vue {

        @Prop({type: Object})
        detail: Detail;

        @Prop({type: Boolean})
        value: boolean;
    }
</script>
