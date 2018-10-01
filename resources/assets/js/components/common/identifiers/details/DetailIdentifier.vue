<template>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {Detail} from "../../../../server/resources";
    import {namespace} from "vuex-class";
    import detailEditDialog from "../../dialogs/DetailEditDialog.vue";
    import detailDeleteDialog from "../../dialogs/DetailDeleteDialog.vue";
    import copyToClipboard from "../../../../mixins/CopyToClipboard.vue";

    const SnackbarModule = namespace('shared/snackbar');
    const RefreshModule = namespace('shared/refresh');

    @Component({
        'components': {
            'b-detail-edit-dialog': detailEditDialog,
            'b-detail-delete-dialog': detailDeleteDialog
        },
        'mixins': [
            copyToClipboard
        ]
    })
    export default class PhoneIdentifier extends Vue {
        @Prop()
        value: Detail;

        @SnackbarModule.Mutation('updateMessage')
        updateMessage: Function;

        @RefreshModule.Mutation('requestRefresh')
        requestRefresh: Function;

        show: boolean = false;

        edit: boolean = false;

        deleteDialog: boolean = false;

        x: Number = 0;
        y: Number = 0;

        copyToClipboard: Function;

        editDetail() {
            this.edit = true;
            this.x = this.$refs.detail ? (this.$refs.detail as HTMLElement).getBoundingClientRect().left - 20 : this.x;
            this.y = this.$refs.detail ? (this.$refs.detail as HTMLElement).getBoundingClientRect().top - 20 : this.y;
        }

        saveDetail(detail) {
            if (detail instanceof Detail) {
                detail.save().then(() => {
                    this.updateMessage('Detail geändert.');
                    this.requestRefresh();
                }).catch((error) => {
                    this.updateMessage('Fehler beim Ändern des Details. Code: ' + error.response.status + ' Message: ' + error.response.statusText);
                });
            }
        }

        deleteDetail() {
            this.$emit('delete', this.value);
            this.value.delete().then(() => {
                this.updateMessage('Detail entfernt.');
                this.requestRefresh();
            }).catch((error) => {
                this.updateMessage('Fehler beim Entfernen des Details. Code: ' + error.response.status + ' Message: ' + error.response.data);
            });
        }
    }
</script>