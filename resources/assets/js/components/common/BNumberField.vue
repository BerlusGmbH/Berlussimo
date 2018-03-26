<template>
    <v-text-field ref="textfield"
                  :value="v"
                  @input="onNumberStringChange"
                  type="text"
                  :autofocus="autofocus"
                  :auto-grow="autoGrow"
                  :box="box"
                  :clearable="clearable"
                  :color="color"
                  :counter="counter"
                  :full-width="fullWidth"
                  :multi-line="multiLine"
                  :placeholder="placeholder"
                  :prefix="prefix"
                  :rows="rows"
                  :single-line="singleLine"
                  :solo="solo"
                  :suffix="suffix"
                  :append-icon="appendIcon"
                  :prepend-icon="prependIcon"
                  :label="label"
                  :readonly="readonly"
                  :disabled="disabled"
                  :tabindex="tabindex"
                  :hide-details="hideDetails"
                  class="b-number-field"
    ></v-text-field>
</template>
<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop} from "vue-property-decorator";
    import Numbro from "../../libraries/numbro";

    @Component
    export default class BNumberField extends Vue {
        @Prop({type: Number})
        value: number;

        @Prop()
        autofocus;

        @Prop()
        autoGrow;

        @Prop({type: Boolean, default: false})
        box;

        @Prop({type: [String, Boolean]})
        clearable;

        @Prop()
        color;

        @Prop()
        counter;

        @Prop()
        fullWidth;

        @Prop()
        multiLine;

        @Prop()
        placeholder;

        @Prop({type: String, default: ''})
        prefix;

        @Prop()
        rows;

        @Prop({type: Boolean, default: false})
        singleLine;

        @Prop({type: Boolean, default: false})
        solo;

        @Prop({type: String, default: ''})
        suffix;

        @Prop({type: String, default: ''})
        appendIcon;

        @Prop({type: String, default: ''})
        prependIcon;

        @Prop({type: String, default: ''})
        label;

        @Prop({type: String, default: ''})
        format;

        @Prop({type: Boolean, default: false})
        readonly;

        @Prop({type: Boolean, default: false})
        disabled;

        @Prop({type: [Number, String], default: 0})
        tabindex;

        @Prop({type: [Boolean, String], default: false})
        hideDetails;

        get v() {
            return Numbro(this.value).format(this.format);
        }

        onNumberStringChange(val) {
            val = Numbro(Numbro().unformat(val)).format(this.format, Math.floor);
            this.$emit('input', Numbro().unformat(val));
            if (this.$refs.textfield) {
                let input: HTMLInputElement | null = (this.$refs.textfield as any).$refs.input;
                if (input) {
                    let position = input.selectionStart;
                    if (input) {
                        if (val !== input.value) {
                            input.value = val;
                            let evt = new Event('input');
                            input.dispatchEvent(evt);
                        }
                        this.$nextTick(() => {
                            if (input) {
                                input.setSelectionRange(position, position);
                            }
                        });
                    }
                }
            }
        }
    }
</script>
<style>
    .b-number-field input {
        text-align: right;
    }
</style>