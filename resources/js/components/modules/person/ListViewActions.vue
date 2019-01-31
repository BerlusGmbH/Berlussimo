<template>
    <div>
        <v-btn @click.native.stop="add = true">
            <v-icon ref="identifier">add</v-icon>
            <v-icon>mdi-account</v-icon>
        </v-btn>
        <b-create-person-dialog :show="add"
                                @close="add = false"
                                @save="onSave"
                                @show="val => {add = val}"
        ></b-create-person-dialog>
    </div>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import CreatePersonDialog from "../../../components/modules/person/dialogs/CreateDialog.vue";
    import EventBus from '../../../EventBus';

    @Component({
        components: {
            'b-create-person-dialog': CreatePersonDialog
        }
    })
    export default class ListViewActions extends Vue {
        add: boolean = false;

        onSave() {
            this.add = false;
            EventBus.$emit('list-view:refetch');
        }
    }
</script>
