<template>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Prop} from "vue-property-decorator";
    import {Detail} from "../../../models";
    import DetailEditDialog from "../dialogs/DetailEditDialog.vue";
    import DetailDeleteDialog from "../dialogs/DetailDeleteDialog.vue";
    import CopyToClipboard from "../../../mixins/CopyToClipboard.vue";
    import DisplaysMessages from "../../../mixins/DisplaysMessages.vue";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../mixins";
    import UpdateDetailMutation from "./UpdateDetailMutation.graphql";
    import DeleteDetailMutation from "./DeleteDetailMutation.graphql";
    import DisplaysErrors from "../../../mixins/DisplaysErrors.vue";
    import {removeObjectFromCache} from "./RemoveObjectFromCache";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {
            'app-detail-edit-dialog': DetailEditDialog,
            'app-detail-delete-dialog': DetailDeleteDialog
        },
        mixins: [
            CopyToClipboard,
            DisplaysMessages,
            DisplaysErrors
        ]
    })
    export default class BaseDetailIdentifier extends Vue implements DisplaysMessagesContract, DisplaysErrorsContract {
        @Prop()
        value: Detail;

        show: boolean = false;

        edit: boolean = false;

        deleteDialog: boolean = false;

        copyToClipboard: Function;

        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        errorMessages: ErrorMessages;

        clearErrorMessages: () => void;

        extractErrorMessages: (error: any) => void;

        loading: boolean = false;

        editDetail() {
            this.edit = true;
        }

        saveDetail(detail) {
            if (detail instanceof Detail) {
                this.clearErrorMessages();
                this.loading = true;
                this.$apollo.mutate({
                    mutation: UpdateDetailMutation,
                    variables: {
                        input: {
                            id: detail.id,
                            value: detail.value,
                            comment: detail.comment
                        }
                    }
                }).then(() => {
                    this.loading = false;
                    this.edit = false;
                    this.showMessage('Detail geändert.');
                }).catch(error => {
                    this.loading = false;
                    this.extractErrorMessages(error);
                    this.showMessage('Fehler beim Ändern des Details. Nachricht: ' + error.message);
                });
            }
        }

        deleteDetail() {
            this.$emit('delete', this.value);
            this.clearErrorMessages();
            this.loading = true;
            const vm = this;
            this.$apollo.mutate({
                mutation: DeleteDetailMutation,
                variables: {
                    id: this.value.id
                },
                update(cache) {
                    removeObjectFromCache((cache as any).data.data, vm.value.__typename + ':' + vm.value.id);
                    return (cache as any).data.delete(vm.value.__typename + ':' + vm.value.id);
                }
            }).then(() => {
                this.loading = false;
                this.deleteDialog = false;
                this.showMessage('Detail entfernt.');
            }).catch(error => {
                this.loading = false;
                this.extractErrorMessages(error);
                this.showMessage('Fehler beim Entfernen des Details. Nachricht: ' + error.message);
            })
        }
    }
</script>
