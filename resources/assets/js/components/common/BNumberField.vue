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

        @Prop()
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

        @Prop()
        prefix;

        @Prop()
        rows;

        @Prop()
        singleLine;

        @Prop()
        solo;

        @Prop()
        suffix;

        @Prop({type: String, default: ''})
        appendIcon;

        @Prop({type: String, default: ''})
        prependIcon;

        @Prop({type: String, default: ''})
        label;

        @Prop({type: String, default: ''})
        format;

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