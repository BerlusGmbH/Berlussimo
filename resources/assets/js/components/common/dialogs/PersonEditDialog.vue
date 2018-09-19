<template>
    <app-edit-dialog
            lazy
            large
            :positionAbsolutley="positionAbsolutley"
            :positionX="positionX"
            :positionY="positionY"
            :show="show"
            @show="$emit('show', $event)"
            @open="onOpen"
            @save="onSave"
    >
        <slot></slot>
        <v-text-field
                slot="input"
                v-model="inputValue.name"
                label="Name"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="inputValue.first_name"
                label="Vorname"
                type="text"
                prepend-icon="mdi-alphabetical"
        ></v-text-field>
        <v-text-field
                slot="input"
                v-model="inputValue.birthday"
                label="Geburtstag"
                type="date"
                prepend-icon="mdi-cake"
        ></v-text-field>
        <v-select v-model="inputValue.sex"
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
    import _ from "lodash";
    import {Person} from "../../../server/resources";
    import {namespace} from "vuex-class";

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component
    export default class PersonEditDialog extends Vue {

        @Prop({type: Object})
        value: Person;

        @Prop()
        large: boolean;

        @Prop()
        type: String;

        @Prop({type: Boolean})
        positionAbsolutley;

        @Prop({type: Number})
        positionX;

        @Prop({type: Number})
        positionY;

        @Prop({type: Boolean})
        show;

        @SnackbarModule.Mutation('updateMessage')
        updateMessage: Function;

        @RefreshModule.Mutation('requestRefresh')
        requestRefresh: Function;

        inputValue: Person = new Person();

        gender: Array<Object> = [
            {value: '', text: 'unbekannt'},
            {value: 'männlich', text: 'männlich'},
            {value: 'weiblich', text: 'weiblich'}
        ];

        onSave() {
            this.$emit('input', this.inputValue);
            this.inputValue.save().then(() => {
                this.updateMessage('Person geändert.');
                this.requestRefresh();
            }).catch((error) => {
                this.updateMessage('Fehler beim Ändern der Person. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
            });
        }

        onOpen() {
            this.inputValue = _.cloneDeep(this.value);
        }
    }
</script>
