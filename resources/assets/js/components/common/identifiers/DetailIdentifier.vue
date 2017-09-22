<template>
    <component :value="value" @input="$emit('input', $event)"
               :is="identifier" @delete="$emit('delete', $event)"
    ></component>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import phoneIdentifier from "./details/PhoneIdentifier.vue";
    import emailIdentifier from "./details/EmailIdentifier.vue";
    import faxIdentifier from "./details/FaxIdentifier.vue";
    import addressIdentifier from "./details/AddressIdentifier.vue"
    import defaultIdentifier from "./details/DefaultIdentifier.vue"
    import {Prop} from "vue-property-decorator";

    @Component({
        components: {
            'Telefon': phoneIdentifier,
            'Handy': phoneIdentifier,
            'Email': emailIdentifier,
            'Fax': faxIdentifier,
            'Zustellanschrift': addressIdentifier,
            'Verzugsanschrift': addressIdentifier,
            'Anschrift': addressIdentifier,
            'default': defaultIdentifier
        }
    })
    export default class Identifier extends Vue {
        @Prop()
        value;

        get identifier() {
            return ['Telefon',
                'Handy',
                'Email',
                'Fax',
                'Zustellanschrift',
                'Verzugsanschrift',
                'Anschrift'
            ].includes(this.value.DETAIL_NAME) ? this.value.DETAIL_NAME : 'default';
        }
    }
</script>