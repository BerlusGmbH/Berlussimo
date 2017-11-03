<template>
    <v-dialog v-model="value" @input="$emit('input', $event)" width="100%" persistent>
        <form>
            <v-card>
                <v-card-title>
                    <span class="headline">
                        <v-icon>mdi-account</v-icon>
                        <v-icon style="margin-left: -10px">mdi-call-merge</v-icon>
                        <v-icon style="margin-left: -10px">mdi-account</v-icon>
                        Personen zusammenführen
                    </span>
                </v-card-title>
                <v-card-text>
                    <v-layout row>
                        <v-flex xs12>
                            <app-entity-select v-model="right" :entities="['person']"></app-entity-select>
                        </v-flex>
                    </v-layout>
                    <v-layout align-center row wrap>
                        <v-flex xs3>
                            <h5>Links</h5>
                        </v-flex>
                        <v-flex xs1>
                        </v-flex>
                        <v-flex xs4>
                            <h5>Zusammen</h5>
                        </v-flex>
                        <v-flex xs1>
                        </v-flex>
                        <v-flex xs3>
                            <h5>Rechts</h5>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field type="text" minlength="1" maxlength="255" id="name" name="name"
                                          :value="left_name" label="Nachname" disabled></v-text-field>
                        </v-flex>
                        <v-flex xs1 class="text-xs-center">
                            <v-icon style="padding-bottom: 22px" @click="fillName(left_name)">mdi-arrow-right</v-icon>
                        </v-flex>
                        <v-flex xs4>
                            <v-text-field type="text" minlength="1" maxlength="255" id="name" name="name"
                                          prepend-icon="mdi-alphabetical" v-model="merged.name"
                                          :rules="name_rules" label="Nachname"></v-text-field>
                        </v-flex>
                        <v-flex xs1 class="text-xs-center">
                            <v-icon style="padding-bottom: 22px" @click="fillName(right_name)">mdi-arrow-left</v-icon>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field type="text" minlength="1" maxlength="255" id="name" name="name"
                                          :value="right_name" label="Nachname" disabled></v-text-field>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field type="text" minlength="1" maxlength="255" id="first_name" name="first_name"
                                          :value="left_first_name" disabled label="Vorname"></v-text-field>
                        </v-flex>
                        <v-flex xs1 class="text-xs-center">
                            <v-icon style="padding-bottom: 22px" @click="fillFirstName(left_first_name)">
                                mdi-arrow-right
                            </v-icon>
                        </v-flex>
                        <v-flex xs4>
                            <v-text-field type="text" minlength="1" maxlength="255" id="first_name" name="first_name"
                                          prepend-icon="mdi-alphabetical" v-model="merged.first_name" class="validate"
                                          label="Vorname"></v-text-field>
                        </v-flex>
                        <v-flex xs1 class="text-xs-center">
                            <v-icon style="padding-bottom: 22px" @click="fillFirstName(right_first_name)">
                                mdi-arrow-left
                            </v-icon>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field type="text" minlength="1" maxlength="255" id="first_name" name="first_name"
                                          :value="right_first_name" label="Vorname" disabled></v-text-field>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field type="date" id="birthday" name="birthday" class="input-group--dirty"
                                          :value="left_birthday" label="Geburtstag" disabled></v-text-field>
                        </v-flex>
                        <v-flex xs1 class="text-xs-center">
                            <v-icon style="padding-bottom: 22px" @click="fillBirthday(left_birthday)">mdi-arrow-right
                            </v-icon>
                        </v-flex>
                        <v-flex xs4>
                            <v-text-field type="date" id="birthday" name="birthday"
                                          prepend-icon="mdi-cake" v-model="merged.birthday"
                                          class="validate input-group--dirty"
                                          label="Geburtstag"></v-text-field>
                        </v-flex>
                        <v-flex xs1 class="text-xs-center">
                            <v-icon style="padding-bottom: 22px" @click="fillBirthday(right_birthday)">mdi-arrow-left
                            </v-icon>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field type="date" id="birthday" name="birthday" class="input-group--dirty"
                                          :value="right_birthday" disabled label="Geburtstag"></v-text-field>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field type="text" minlength="1" maxlength="255" id="name" name="name"
                                          :value="left_sex" disabled label="Geschlecht"></v-text-field>
                        </v-flex>
                        <v-flex xs1 class="text-xs-center">
                            <v-icon style="padding-bottom: 22px" @click="fillSex(left_sex)">mdi-arrow-right</v-icon>
                        </v-flex>
                        <v-flex xs4>
                            <v-select v-model="merged.sex" :items="gender" prepend-icon="mdi-alphabetical"
                                      label="Geschlecht" class="validate" id="sex" name="sex"
                            ></v-select>
                        </v-flex>
                        <v-flex xs1 class="text-xs-center">
                            <v-icon style="padding-bottom: 22px" @click="fillSex(right_sex)">mdi-arrow-left</v-icon>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field type="text" minlength="1" maxlength="255" id="name" name="name"
                                          :value="right_sex" disabled label="Geschlecht"></v-text-field>
                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn flat @click="$emit('input', false)">Abbrechen</v-btn>
                    <v-btn class="red" @click.native="onSubmit" :disabled="!right || loading"
                           :loading="loading"
                    >
                        <v-icon>mdi-call-merge</v-icon>
                        Zusammenführen
                    </v-btn>
                </v-card-actions>
            </v-card>
        </form>
    </v-dialog>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import {Mutation, namespace} from "vuex-class";
    import {Prop, Watch} from "vue-property-decorator";
    import EntitySelect from '../EntitySelect.vue';
    import {Person} from "server/resources/models";
    import axios from "libraries/axios";
    import {AxiosError} from "axios";

    const SnackbarMutation = namespace('shared/snackbar', Mutation);

    @Component({
        components: {
            'app-entity-select': EntitySelect
        }
    })
    export default class PersonMergeDialog extends Vue {
        mounted() {
            this.fillMerged();
        }

        @Prop({default: false})
        value: boolean;

        @SnackbarMutation('updateMessage')
        showSnack: Function;

        @Prop({type: Object, default: () => new Person()})
        left: Person;

        right: Person | null = null;

        error: Object = {};

        loading: boolean = false;

        merged: Object = {
            name: '',
            first_name: null,
            birthday: null,
            sex: null
        };

        onSubmit() {
            this.loading = true;
            let vm = this;
            if (this.right) {
                axios.get('/api/v1/persons/' + this.left.id + '/merge/' + this.right.id,
                        {
                            params: this.merged
                        }
                ).then(() => {
                    if (
                            this.left.id > (this.right as Person).id
                            && window.location.pathname === '/persons/' + vm.left.id
                    ) {
                        this.$router.push({name: 'web.persons.show', params: {id: String((this.right as Person).id)}});
                    }
                    this.$emit('input', false);
                    this.loading = false;
                    this.right = null;
                    this.showSnack(
                            'Personen werden zusammengeführt.'
                    );
                }).catch((error: AxiosError) => {
                    if (error.response) {
                        switch (error.response.status) {
                            case 422:
                                vm.error = error.response.data;
                                break;
                            default:
                                this.showSnack(
                                        'Fehler beim Übertragen: '
                                        + error.response.status + ' ' + error.response.statusText
                                );
                        }
                    }
                    vm.loading = false;
                });
            }
        }

        fillName(name) {
            this.error['name'] = null;
            this.$set(this.merged, 'name', name);
        }

        fillFirstName(first_name) {
            this.$set(this.merged, 'first_name', first_name);
        }

        fillBirthday(birthday) {
            this.$set(this.merged, 'birthday', birthday);
        }

        fillSex(sex) {
            this.$set(this.merged, 'sex', sex);
        }

        fillMerged() {
            this.fillName(this.merged_name);
            this.fillFirstName(this.merged_first_name);
            this.fillBirthday(this.merged_birthday);
            this.fillSex(this.merged_sex);
        }

        @Watch('left')
        onLeftChange() {
            this.fillMerged();
        }

        @Watch('right')
        onRightChange() {
            this.fillMerged();
        }

        gender: Array<Object> = [
            {value: '', text: 'unbekannt'},
            {value: 'männlich', text: 'männlich'},
            {value: 'weiblich', text: 'weiblich'}
        ];

        get merged_name() {
            if (this.right_name && !this.left_name) {
                return this.right_name;
            }
            if (!this.right_name && this.left_name) {
                return this.left_name;
            }
            if (this.right_name === this.left_name) {
                return this.left_name;
            }
            return '';
        }

        get merged_first_name() {
            if (this.right_first_name && !this.left_first_name) {
                return this.right_first_name;
            }
            if (!this.right_first_name && this.left_first_name) {
                return this.left_first_name;
            }
            if (this.right_first_name === this.left_first_name) {
                return this.left_first_name;
            }
            return '';
        }

        get merged_birthday() {
            if (this.right_birthday && !this.left_birthday) {
                return this.right_birthday;
            }
            if (!this.right_birthday && this.left_birthday) {
                return this.left_birthday;
            }
            if (this.right_birthday === this.left_birthday) {
                return this.left_birthday;
            }
            return '';
        }

        get merged_sex() {
            if (this.right_sex && !this.left_sex) {
                return this.right_sex;
            }
            if (!this.right_sex && this.left_sex) {
                return this.left_sex;
            }
            if (this.right_sex === this.left_sex) {
                return this.left_sex;
            }
            return '';
        }

        get left_name() {
            return this.left ? this.left.name : '';
        }

        get left_first_name() {
            return this.left ? this.left.first_name : '';
        }

        get left_birthday() {
            return this.left && this.left.birthday ? this.left.birthday : '';
        }

        get left_sex() {
            return this.left && this.left.sex ? this.left.sex : '';
        }

        get right_name() {
            return this.right ? this.right.name : '';
        }

        get right_first_name() {
            return this.right ? this.right.first_name : '';
        }

        get right_birthday() {
            return this.right && this.right.birthday ? this.right.birthday : '';
        }

        get right_sex() {
            return this.right && this.right.sex ? this.right.sex : '';
        }

        get name_rules() {
            let error = this.error['name'];
            return [() => {
                let value = true;
                if (error) {
                    value = error.join(' ');
                }
                return value;
            }];
        }
    }
</script>