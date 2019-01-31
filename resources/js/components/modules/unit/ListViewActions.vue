<template>
    <div>
        <v-btn @click.native.stop="add = true">
            <v-icon ref="identifier">add</v-icon>
            <v-icon>mdi-cube</v-icon>
        </v-btn>
        <b-create-unit-dialog :show="add"
                              @close="add = false"
                              @save="onSave"
                              @show="val => {add = val}"
        ></b-create-unit-dialog>
    </div>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import CreateDialog from "../../../components/modules/unit/dialogs/CreateDialog.vue";
    import EventBus from '../../../EventBus';

    @Component({
        components: {
            'b-create-unit-dialog': CreateDialog
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
