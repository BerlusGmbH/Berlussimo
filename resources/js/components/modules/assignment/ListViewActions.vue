<template>
    <div>
        <v-btn @click.native.stop="add = true">
            <v-icon ref="identifier">add</v-icon>
            <v-icon>mdi-clipboard</v-icon>
        </v-btn>
        <b-create-assignment-dialog :value="add"
                                    @close="add = false"
                                    @save="onSave"
                                    @show="val => {add = val}"
        ></b-create-assignment-dialog>
    </div>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import CreateDialog from "../../../components/modules/assignment/dialogs/CreateDialog.vue";
    import EventBus from '../../../EventBus';

    @Component({
        components: {
            'b-create-assignment-dialog': CreateDialog
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
