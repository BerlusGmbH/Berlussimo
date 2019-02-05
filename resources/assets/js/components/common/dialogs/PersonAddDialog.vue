<template>
    <app-edit-dialog
            lazy
            large
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
    import {Person} from "../../../server/resources";
    import {namespace} from "vuex-class";

    const Snackbar = namespace('shared/snackbar');
    const Refresh = namespace('shared/refresh');

    @Component
    export default class PersonAddDialog extends Vue {

        value: Person = new Person();

        @Prop()
        large: boolean;

        @Prop({type: Boolean})
        show;

        @Snackbar.Mutation('updateMessage')
        updateMessage: Function;

        @Refresh.Mutation('requestRefresh')
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
