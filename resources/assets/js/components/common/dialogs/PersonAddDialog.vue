<template>
    <app-edit-dialog
            lazy
            large
            :positionAbsolutley="positionAbsolutley"
            :positionX="positionX"
            :positionY="positionY"
            :show="show"
            @show="$emit('show', $event)"
            @save="onSave"
    >
        <slot></slot>
        <v-text-field
                slot="input"
                v-model="value.name"
                label="Name"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="value.first_name"
                label="Vorname"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="value.birthday"
                label="Geburtstag"
                type="date"
                prepend-icon="mdi-cake"
        ></v-text-field>
        <v-select v-model="value.sex"
                  :items="gender"
                  prepend-icon="mdi-alphabetical"
                  label="Geschlecht"
                  slot="input"
        ></v-select>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import {Person} from "server/resources/models";
    import {Mutation, namespace} from "vuex-class";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);
    const RefreshMutation = namespace('shared/refresh', Mutation);

    @Component
    export default class PersonAddDialog extends Vue {

        value: Person = new Person();

        @Prop()
        large: boolean;

        @Prop({type: Boolean})
        positionAbsolutley;

        @Prop({type: Number})
        positionX;

        @Prop({type: Number})
        positionY;

        @Prop({type: Boolean})
        show;

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        @RefreshMutation('requestRefresh')
        requestRefresh: Function;

        gender: Array<Object> = [
            {value: '', text: 'unbekannt'},
            {value: 'männlich', text: 'männlich'},
            {value: 'weiblich', text: 'weiblich'}
        ];

        onSave() {
            this.$emit('input', this.value);
            this.value.create().then(() => {
                this.updateMessage('Person erstellt.');
                this.requestRefresh();
            }).catch((error) => {
                this.$emit('show', true);
                this.updateMessage('Fehler beim Erstellen der Person. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }
    }
</script>
