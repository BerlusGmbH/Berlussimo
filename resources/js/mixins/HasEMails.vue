<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import PropertyEMails from "./PropertyEMailsQuery.graphql";
    import HouseEMails from "./HouseEMailsQuery.graphql";
    import UnitEMails from "./UnitEMailsQuery.graphql";
    import {DisplaysMessagesContract} from "./DisplaysMessagesContract";
    import DisplaysMessages from "./DisplaysMessages.vue";

    @Component({
        mixins: [DisplaysMessages]
    })
    export default class HasEMails extends Vue implements DisplaysMessagesContract {

        showMessage;

        value;

        emails: {
            tenants: any[];
            owners: any[];
        } = {
            tenants: [],
            owners: []
        };

        loading: boolean = false;

        loadEMails() {
            let loadEMails, index;
            this.loading = true;
            switch (this.value.__typename) {
                case 'Property':
                    loadEMails = PropertyEMails;
                    index = 'property';
                    break;
                case 'House':
                    loadEMails = HouseEMails;
                    index = 'house';
                    break;
                case 'Unit':
                    loadEMails = UnitEMails;
                    index = 'unit';
                    break;
            }
            this.$apollo.query({
                query: loadEMails,
                variables: {
                    id: this.value.id
                }
            }).then(result => {
                this.loading = false;
                this.emails.tenants = result.data[index].tenantsEMails;
                this.emails.owners = result.data[index].homeOwnersEMails;
            }).catch(_error => {
                this.loading = false;
            });
        }

        countEMails(recipients): number {
            if (this.emails[recipients]) {
                return this.emails[recipients].length;
            }
            return 0;
        }

        sendEMails(recipients) {
            if (this.emails[recipients] && this.emails[recipients].length > 0) {
                window.open('mailto:?bcc=' + this.emails[recipients].reduce((all, current) => {
                    if (all === null || all === '') {
                        return current.value
                    }
                    return all + ',' + current.value
                }, ''))
            } else {
                this.showMessage('Es sind keine E-Mail-Adressen hinterlegt.');
            }
        }
    }
</script>
