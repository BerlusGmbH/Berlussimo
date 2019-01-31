<template>
    <b-entity-select
            :entities="variable.types"
            :label="variable.label"
            :multiple="variable.list"
            :prepend-icon="variable.icon"
            :value="value"
            @input="$emit('input', $event)"
            clearable
            v-if="variable.kind === 'EntitySelect'"
    ></b-entity-select>
    <v-text-field
            :label="variable.label"
            :prepend-icon="variable.icon"
            :value="value"
            @input="$emit('input', $event)"
            clearable
            type="text"
            v-else-if="['ID', 'String'].includes(variable.kind)"
    ></v-text-field>
    <v-text-field
            :label="variable.label"
            :prepend-icon="variable.icon"
            :value="value"
            @input="$emit('input', $event)"
            clearable
            step="1"
            type="number"
            v-else-if="variable.kind === 'Int'"
    ></v-text-field>
    <v-text-field
            :label="variable.label"
            :prepend-icon="variable.icon"
            :value="value"
            @input="$emit('input', $event)"
            clearable
            step="0.01"
            type="number"
            v-else-if="variable.kind === 'Float'"
    ></v-text-field>
    <b-date-picker
            :label="variable.label"
            :prepend-icon="variable.icon"
            :value="value"
            @input="$emit('input', $event)"
            v-else-if="variable.kind === 'Date'"
    ></b-date-picker>
    <v-select
            :items="[{text: 'Ja', value: true}, {text: 'Nein', value: false}]"
            :label="variable.label"
            :prepend-icon="variable.icon"
            :value="value"
            @input="$emit('input', $event)"
            clearable
            v-else-if="variable.kind === 'Boolean'"
    ></v-select>
    <v-select
            :items="items"
            :label="variable.label"
            :multiple="variable.list"
            :prepend-icon="variable.icon"
            :value="value"
            @input="$emit('input', $event)"
            clearable
            v-else
    ></v-select>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import EntitySelect from "../../common/EntitySelect";
    import {Prop} from "vue-property-decorator";
    import TypeIntrospectionQuery from "./TypeIntrospectionQuery.graphql"
    import BDatePicker from "./BDatePicker.vue";

    @Component({
        components: {
            'b-date-picker': BDatePicker,
            'b-entity-select': EntitySelect
        },
        apollo: {
            items: {
                query: TypeIntrospectionQuery,
                skip(this: VariableInputField) {
                    if (!this.variable) {
                        return true;
                    }
                    return ['ID', 'String', 'Int', 'Float', 'Boolean', 'EntitySelect'].includes(this.variable.kind);
                },
                variables(this: VariableInputField) {
                    return {name: this.variable.kind};
                },
                update(data) {
                    if (data.__type.enumValues) {
                        return data.__type.enumValues.map(value => {
                            return {
                                text: value.description,
                                value: value.name
                            };
                        });
                    }
                    return [];
                }
            }
        }
    })
    export default class VariableInputField extends Vue {
        @Prop()
        variable: any;

        @Prop()
        value: any;

        items = [];
    }
</script>
