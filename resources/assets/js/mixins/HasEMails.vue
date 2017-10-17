<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import {Mutation, namespace} from "vuex-class";
    import axios from "libraries/axios";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);

    @Component
    export default class HasEMails extends Vue {

        @SnackbarMutation('updateMessage')
        updateMessage: Function;

        value;

        emails: {
            'tenants': Array<string>;
            'owners': Array<string>;
        } = {
            'tenants': [],
            'owners': []
        };

        loadEMails(receipients) {
            axios.get(this.value.getApiBaseUrl() + '/' + this.value.getID() + '/' + receipients + '/emails').then((response) => {
                this.emails[receipients] = response.data;
            });
        }

        countEMails(receipients): string {
            if (this.emails[receipients]) {
                return '(' + this.emails[receipients].length + ')';
            }
            return '';
        }

        sendEMails(receipients) {
            if (this.emails[receipients] && this.emails[receipients].length > 0) {
                window.open('mailto:?bcc=' + this.emails[receipients].join(','))
            } else {
                this.updateMessage('Es sind keine E-Mail-Adressen hinterlegt.');
            }
        }
    }
</script>