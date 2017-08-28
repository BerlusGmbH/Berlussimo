<template>
    <div class="identifier">
        <span style="padding-right: 10px">
            <v-icon class="identifier-icon">mdi-phone</v-icon>
            <v-menu offset-y v-model="showDetailMenu" :position-absolutely="true">
                <span slot="activator"
                      style="cursor: pointer">{{entity.DETAIL_INHALT}}</span>
            <v-list>
                <v-list-tile @click="copyToClipboard(entity.DETAIL_INHALT)">
                    <v-list-tile-avatar>
                        <v-icon>mdi-content-copy</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>Kopieren</v-list-tile-title>
                </v-list-tile>
                <v-list-tile @click="call">
                    <v-list-tile-avatar>
                        <v-icon>mdi-phone</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>Anrufen</v-list-tile-title>
                </v-list-tile>
                <v-list-tile>
                    <v-list-tile-avatar>
                        <v-icon>edit</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>Bearbeiten</v-list-tile-title>
                </v-list-tile>
            </v-list>
        </v-menu>
        </span>
        <span v-if="entity.DETAIL_BEMERKUNG">
            <v-icon>mdi-note</v-icon>
            <v-menu offset-y v-model="showNoteMenu" :position-absolutely="true">
                <span slot="activator"
                      style="cursor: pointer">{{entity.DETAIL_BEMERKUNG}}</span>
            <v-list>
                <v-list-tile @click="copyToClipboard(entity.DETAIL_BEMERKUNG)">
                    <v-list-tile-avatar>
                        <v-icon>mdi-content-copy</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>Kopieren</v-list-tile-title>
                </v-list-tile>
                <v-list-tile>
                    <v-list-tile-avatar>
                        <v-icon>edit</v-icon>
                    </v-list-tile-avatar>
                    <v-list-tile-title>Bearbeiten</v-list-tile-title>
                </v-list-tile>
            </v-list>
        </v-menu>
        </span>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {Detail} from "../../../../server/resources";
    import {Mutation, namespace, State} from "vuex-class";
    import axios from "../../../../libraries/axios"

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const WorkplaceState = namespace('shared/workplace', State);

    @Component
    export default class PhoneIdentifier extends Vue {
        @Prop()
        entity: Detail;

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @WorkplaceState('hasPhone')
        workplaceHasPhone: boolean;

        showDetailMenu: boolean = false;
        showNoteMenu: boolean = false;
        x: number = 0;
        y: number = 0;

        copyToClipboard(value) {
            let text = document.createElement("input");
            document.body.appendChild(text);
            text.value = value;
            text.select();
            document.execCommand('copy');
            document.body.removeChild(text);
            this.updateMessage('Detail in die Zwichenablage kopiert.');
        }

        callDirect() {
            window.open('tel:' + this.entity.DETAIL_INHALT);
        }

        callViaServer() {
            axios.get('/api/v1/call/' + this.entity.DETAIL_ID).then(() => {
                this.updateMessage('Nummer wird gewählt.');
            }).catch((error) => {
                this.updateMessage('Fehler beim wählen. Code: ' + error.response.status + ' Message: ' + error.response.data);
            });
        }

        call() {
            if (this.workplaceHasPhone) {
                this.callViaServer();
            } else {
                this.callDirect();
            }
        }
    }
</script>

<style>
    .identifier-icon {
        position: absolute;
        left: 0;
    }

    .identifier i {
        font-size: inherit;
    }

    .identifier {
        padding-left: 1.2em;
        position: relative;
        display: inline-block
    }
</style>