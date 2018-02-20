<template>
    <app-edit-dialog
            lazy
            :large="large"
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
                v-model="inputValue"
                single-line
                :type="type"
                :prepend-icon="prependIcon"
        ></v-text-field>
    </app-edit-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";

    @Component
    export default class TextFieldEditDialog extends Vue {
        @Prop()
        value: String;

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

        @Prop({type: String})
        prependIcon;

        inputValue: String = '';

        onSave() {
            this.$emit('input', this.inputValue);
        }

        onOpen() {
            this.inputValue = this.value;
        }
    }
</script>
