<template>
    <v-fab-transition>
        <v-speed-dial
                v-if="person"
                :value="value"
                @input="$emit('input', $event)"
                bottom
                right
                fixed
                direction="top"
                hover
                transition="slide-y-reverse-transition"
                class="mb-5 mr-2"
        >
            <v-btn
                    slot="activator"
                    class="primary"
                    dark
                    fab
                    :value="value"
                    @input="$emit('input', $event)"
            >
                <v-icon>mdi-account-edit</v-icon>
                <v-icon>close</v-icon>
            </v-btn>

            <v-btn
                    fab
                    dark
                    small
            >
                <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn @click="open('/details?option=details_hinzu&detail_tabelle=PERSON&detail_id=' + person.id)"
                   fab
                   dark
                   small
            >
                <v-icon>mdi-table</v-icon>
            </v-btn>
            <v-btn
                    fab
                    dark
                    small
            >
                <v-icon>mdi-worker</v-icon>
            </v-btn>
            <v-btn
                    fab
                    dark
                    small
            >
                <v-icon>mdi-lock</v-icon>
            </v-btn>
            <v-btn
                    fab
                    dark
                    small
                    @click="$emit('openMergeDialog')"
            >
                <v-icon>mdi-call-merge</v-icon>
            </v-btn>
        </v-speed-dial>
    </v-fab-transition>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {namespace, State} from "vuex-class";
    import {Person} from "../../../../server/resources/models";

    const PersonShowState = namespace('modules/personen/show', State);

    @Component
    export default class Fab extends Vue {
        @Prop({default: false})
        value: boolean;

        @PersonShowState('person')
        person: Person;

        open(url) {
            window.open(url, '_self');
        }
    }
</script>