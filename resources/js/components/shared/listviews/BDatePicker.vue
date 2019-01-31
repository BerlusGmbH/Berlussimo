<template>
    <v-menu
        :close-on-content-click="false"
        full-width
        lazy
        min-width="290px"
        offset-y
        transition="scale-transition"
        v-model="open"
    >
        <template v-slot:activator="{ on }">
            <v-text-field
                :clearable="clearable"
                :label="label"
                :prepend-icon="prependIcon"
                :value="date"
                @click:clear="onClear"
                readonly
                v-on="on"
            ></v-text-field>
        </template>
        <v-tabs
            grow
            ref="tabs"
            v-model="active"
        >
            <v-tab ripple>
                Absolut
            </v-tab>
            <v-tab ripple>
                Relativ
            </v-tab>
            <v-tab-item>
                <v-date-picker @input="open = false; $emit('input', absoluteDate)"
                               v-model="absoluteDate"></v-date-picker>
            </v-tab-item>
            <v-tab-item>
                <v-card>
                    <v-card-text>
                        <v-radio-group @change="open = false; $emit('input', relativeDate)"
                                       v-model="relativeDate">
                            <v-radio
                                label="Heute"
                                value="today"
                            ></v-radio>
                            <v-radio
                                label="Gestern"
                                value="yesterday"
                            ></v-radio>
                            <v-radio
                                label="Morgen"
                                value="tomorrow"
                            ></v-radio>
                            <v-radio
                                label="Erster des Monats"
                                value="first day of"
                            ></v-radio>
                            <v-radio
                                label="Letzter des Monats"
                                value="last day of"
                            ></v-radio>
                        </v-radio-group>
                    </v-card-text>
                </v-card>
            </v-tab-item>
        </v-tabs>
    </v-menu>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Prop, Watch} from "vue-property-decorator";
    import moment from "../../../libraries/moment";

    @Component
    export default class BDatePicker extends Vue {
        @Prop({type: Boolean, default: true})
        clearable: boolean;

        @Prop({type: String, default: ""})
        label: string;

        @Prop({type: String, default: null})
        prependIcon: string | null;

        @Prop({type: String})
        value: any;

        open: boolean = false;
        absoluteDate: string | null = null;
        relativeDate: string | null = null;
        active: number = 0;
        empty: boolean = true;

        @Watch('open')
        onOpenChange(value) {
            if (value) {
                const vm = this;
                setTimeout(() => {
                    (vm.$refs.tabs as any).callSlider()
                }, 500);
            }
        }

        @Watch('value', {immediate: true})
        onValueChange(value) {
            if (value === null) {
                this.empty = true;
            }
            if (typeof value !== "string") {
                return;
            }
            this.empty = false;
            const date = moment(value, "YYYY-MM-DD", true);
            if (date.isValid()) {
                this.active = 0;
                this.absoluteDate = value;
                return;
            }
            const values = [
                "today",
                "tomorrow",
                "yesterday",
                "first day of",
                "last day of"
            ];
            if (values.includes(value.toLowerCase())) {
                this.active = 1;
                this.relativeDate = value;
            }
        }

        onClear() {
            this.empty = true;
            this.$emit('input', null);
        }

        get date() {
            if (this.empty) {
                return null;
            }
            switch (this.active) {
                case 0:
                    return this.absoluteDate;
                case 1:
                    return this.relativeDate;
            }
            return this.absoluteDate;
        }
    }
</script>
