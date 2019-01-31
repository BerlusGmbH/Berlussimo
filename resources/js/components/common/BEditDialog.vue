<script lang="ts">
    import Vue from "../../imports";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";

    @Component({extends: Vue.component('VEditDialog')})
    export default class BEditDialog extends Vue {
        transition;
        isActive;
        lazy;
        cancel;
        focus;
        large;
        dark;
        light;
        themeClasses;
        genContent;
        originalValue;

        @Prop({type: String, default: 'Speichern'})
        saveText;

        @Prop({type: String, default: 'Abbrechen'})
        cancelText;

        @Prop({type: Boolean})
        show;

        @Prop({type: Boolean})
        persistent;

        @Prop({default: null})
        returnValue;

        @Prop({type: Boolean, default: false})
        loading;

        @Watch('show')
        onShowChange(val) {
            this.isActive = val;
        }

        genButton(fn, text, emphasize = false) {
            return this.$createElement('v-btn', {
                'class': {'red': emphasize},
                props: {
                    flat: !emphasize,
                    light: false,
                    disabled: emphasize ? this.loading : false
                },
                on: {click: fn}
            }, text)
        }

        genActions() {
            return this.$createElement('div', {
                'class': ['small-dialog__actions', 'text-xs-right']
            }, [
                this.genButton(this.cancel, this.cancelText),
                this.genButton(() => {
                    this.save(this.returnValue);
                    this.$emit('save', true);
                }, this.saveText, true)
            ])
        }

        genLoadingIndicator() {
            return this.$createElement('v-progress-linear', {
                props: {
                    indeterminate: true,
                    active: !!this.loading
                }
            })
        }

        save(value: any) {
            this.originalValue = value;
        }

        render(h) {
            return h('v-menu', {
                staticClass: 'v-small-dialog',
                class: this.themeClasses,
                props: {
                    contentClass: 'v-small-dialog__content',
                    transition: this.transition,
                    origin: 'top left',
                    right: true,
                    value: this.isActive,
                    closeOnClick: !this.persistent,
                    closeOnContentClick: false,
                    lazy: this.lazy,
                    light: this.light,
                    dark: this.dark
                },
                on: {
                    input: val => (this.isActive = val)
                }
            }, [
                h('a', {
                    slot: 'activator'
                }, this.$slots.default),
                this.genContent(),
                this.genLoadingIndicator(),
                this.large ? this.genActions() : null
            ])
        }
    }
</script>