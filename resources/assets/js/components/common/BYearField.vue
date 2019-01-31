<template>
    <v-text-field ref="textfield"
                  :value="inputValue"
                  @input="onNumberStringChange"
                  type="text"
                  :autofocus="autofocus"
                  :auto-grow="autoGrow"
                  :box="box"
                  :clearable="clearable"
                  :color="color"
                  :counter="counter"
                  :full-width="fullWidth"
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
    import {Prop, Watch} from "vue-property-decorator";
    import Moment from "../../libraries/moment";

    @Component
    export default class BNumberField extends Vue {
        @Prop({type: String})
        value: string;

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

        @Prop({type: String, default: 'YYYY'})
        format;

        inputValue: string = '';

        mounted() {
            if (this.value) {
                this.onValueChange(this.value);
            }
        }

        @Watch('value')
        onValueChange(val) {
            this.inputValue = Moment(val + '-01-01').format(this.format);
            ;
        }

        onNumberStringChange(val) {
            if (!val) return;
            val = val.replace(/\D/g, '');
            if (isNaN(Number.parseInt(val))) {
                val = Moment().format(this.format);
            } else {
                if (val.length < 4) {
                    while (val.length < 4) {
                        val += '0';
                    }
                } else {
                    val = val.substr(0, 4);
                }
                val = Moment(val + '-01-01').format(this.format);
            }
            if (this.$refs.textfield) {
                let input: HTMLInputElement | null = (this.$refs.textfield as any).$refs.input;
                if (input) {
                    let position = input.selectionStart;
                    if (input) {
                        if (val !== input.value) {
                            input.value = val;
                            let evt = new Event('input');
                            input.dispatchEvent(evt);
                        } else {
                            this.$emit('input', val);
                        }
                        this.$nextTick(() => {
                            if (input && position) {
                                input.setSelectionRange(position, position);
                            }
                        });
                    }
                }
            }
        }
    }
</script>