<template>
    <component :entity="entity" :is="entity.DETAIL_NAME"></component>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from "vue";
    import phoneIdentifier from "./details/PhoneIdentifier.vue";
    import emailIdentifier from "./details/EmailIdentifier.vue";
    import faxIdentifier from "./details/FaxIdentifier.vue";
    import addressIdentifier from "./details/AddressIdentifier.vue"
    import {Prop} from "vue-property-decorator";

    @Component({
        components: {
            'Telefon': phoneIdentifier,
            'Handy': phoneIdentifier,
            'Email': emailIdentifier,
            'Fax': faxIdentifier,
            'Zustellanschrift': addressIdentifier,
            'Verzugsanschrift': addressIdentifier,
            'Anschrift': addressIdentifier
        }
    })
    export default class Identifier extends Vue {
        @Prop()
        entity;
    }
</script>