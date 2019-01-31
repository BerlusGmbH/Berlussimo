<template>
    <v-dialog @input="$emit('input', $event)"
              lazy
              persistent
              v-model="value"
              width="100%"
    >
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
                            <b-entity-select :entities="['Person']" v-model="right"></b-entity-select>
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
                            <v-text-field :value="left_name" disabled id="left_name" label="Nachname" maxlength="255"
                                          minlength="1" name="name" type="text"></v-text-field>
                        </v-flex>
                        <v-flex class="text-xs-center" xs1>
                            <v-icon @click="fillName(left_name)" style="padding-bottom: 22px">mdi-arrow-right</v-icon>
                        </v-flex>
                        <v-flex xs4>
                            <v-text-field id="name" label="Nachname" maxlength="255" minlength="1" name="name"
                                          prepend-icon="mdi-alphabetical" type="text"
                                          :error-messages="errorMessages.for('input.person.lastName')"
                                          v-model="merged.lastName"></v-text-field>
                        </v-flex>
                        <v-flex class="text-xs-center" xs1>
                            <v-icon @click="fillName(right_name)" style="padding-bottom: 22px">mdi-arrow-left</v-icon>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field :value="right_name" disabled id="right_name" label="Nachname" maxlength="255"
                                          minlength="1" name="name" type="text"></v-text-field>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field :value="left_first_name" disabled id="left_first_name" label="Vorname"
                                          maxlength="255"
                                          minlength="1" name="first_name" type="text"></v-text-field>
                        </v-flex>
                        <v-flex class="text-xs-center" xs1>
                            <v-icon @click="fillFirstName(left_first_name)" style="padding-bottom: 22px">
                                mdi-arrow-right
                            </v-icon>
                        </v-flex>
                        <v-flex xs4>
                            <v-text-field class="validate" id="first_name" label="Vorname" maxlength="255" minlength="1"
                                          name="first_name" prepend-icon="mdi-alphabetical" type="text"
                                          :error-messages="errorMessages.for('input.person.firstName')"
                                          v-model="merged.firstName"></v-text-field>
                        </v-flex>
                        <v-flex class="text-xs-center" xs1>
                            <v-icon @click="fillFirstName(right_first_name)" style="padding-bottom: 22px">
                                mdi-arrow-left
                            </v-icon>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field :value="right_first_name" disabled id="right_first_name" label="Vorname"
                                          maxlength="255"
                                          minlength="1" name="first_name" type="text"></v-text-field>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field :value="left_birthday" class="input-group--dirty" disabled id="left_birthday"
                                          label="Geburtstag" name="birthday" type="date"></v-text-field>
                        </v-flex>
                        <v-flex class="text-xs-center" xs1>
                            <v-icon @click="fillBirthday(left_birthday)" style="padding-bottom: 22px">mdi-arrow-right
                            </v-icon>
                        </v-flex>
                        <v-flex xs4>
                            <v-text-field class="validate input-group--dirty" id="birthday" label="Geburtstag"
                                          name="birthday" prepend-icon="mdi-cake"
                                          type="date"
                                          :error-messages="errorMessages.for('input.person.birthday')"
                                          v-model="merged.birthday"></v-text-field>
                        </v-flex>
                        <v-flex class="text-xs-center" xs1>
                            <v-icon @click="fillBirthday(right_birthday)" style="padding-bottom: 22px">mdi-arrow-left
                            </v-icon>
                        </v-flex>
                        <v-flex xs3>
                            <v-text-field :value="right_birthday" class="input-group--dirty" disabled
                                          id="right_birthday"
                                          label="Geburtstag" name="birthday" type="date"></v-text-field>
                        </v-flex>
                        <v-flex xs3>
                            <v-select :items="gender" :value="left_sex" disabled
                                      id="left_sex" label="Geschlecht" type="text"></v-select>
                        </v-flex>
                        <v-flex class="text-xs-center" xs1>
                            <v-icon @click="fillGender(left_sex)" style="padding-bottom: 22px">mdi-arrow-right</v-icon>
                        </v-flex>
                        <v-flex xs4>
                            <v-select :items="gender" class="validate" id="sex"
                                      label="Geschlecht" name="sex" prepend-icon="mdi-alphabetical"
                                      :error-messages="errorMessages.for('input.person.gender')"
                                      v-model="merged.gender"
                            ></v-select>
                        </v-flex>
                        <v-flex class="text-xs-center" xs1>
                            <v-icon @click="fillGender(right_sex)" style="padding-bottom: 22px">mdi-arrow-left</v-icon>
                        </v-flex>
                        <v-flex xs3>
                            <v-select :items="gender" :value="right_sex" disabled
                                      id="right_sex" label="Geschlecht" type="text"></v-select>
                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="$emit('input', false)" flat>Abbrechen</v-btn>
                    <v-btn :disabled="!right || loading" :loading="loading" @click.native="onSubmit"
                           class="red"
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
    import {Prop, Watch} from "vue-property-decorator";
    import EntitySelect from '../../../common/EntitySelect.vue';
    import {Person} from "../../../../models";
    import MergeMutation from "./MergeMutation.graphql"
    import PersonQuery from "../../../shared/breadcrumbs/PersonQuery.graphql";
    import {DisplaysErrorsContract, DisplaysMessagesContract, ErrorMessages} from "../../../../mixins";
    import DisplaysErrors from "../../../../mixins/DisplaysErrors.vue";
    import DisplaysMessages from "../../../../mixins/DisplaysMessages.vue";
    import {ApolloQueryResult} from "apollo-client";

    @Component({
        components: {
            'b-entity-select': EntitySelect
        },
        mixins: [DisplaysErrors, DisplaysMessages],
        apollo: {
            left: {
                query: PersonQuery,
                skip() {
                    return !(this.leftId && this.value);
                },
                variables() {
                    return {
                        id: this.leftId
                    }
                },
                fetchPolicy: "cache-and-network"
            }
        }
    })
    export default class MergeDialog extends Vue implements DisplaysErrorsContract, DisplaysMessagesContract {
        mounted() {
            this.fillMerged();
        }

        @Prop({default: false})
        value: boolean;

        errorMessages: ErrorMessages;
        clearErrorMessages: () => void;
        extractErrorMessages: (error: any) => void;
        showMessage: <R = any>(message: string) => Promise<ApolloQueryResult<R>>;

        @Prop({type: String, default: null})
        leftId: string | null;

        left: Person = new Person();

        right: Person | null = null;

        error: Object = {};

        loading: boolean = false;

        merged: {
            lastName: string,
            firstName: string | null,
            birthday: string | null,
            gender: string | null
        } = {
            lastName: '',
            firstName: null,
            birthday: null,
            gender: null
        };

        onSubmit() {
            this.loading = true;
            if (this.right) {
                this.clearErrorMessages();
                this.$apollo.mutate({
                    mutation: MergeMutation,
                    variables: {
                        input: {
                            ids: [this.left.id, this.right.id],
                            person: this.merged
                        }
                    }
                }).then(() => {
                    if (
                        this.left.id > (this.right as Person).id
                        && window.location.pathname === '/persons/' + this.left.id
                    ) {
                        this.$router.push({
                            name: 'web.persons.show',
                            params: {
                                id: String((this.right as Person).id)
                            }
                        }).catch(_err => {
                        });
                    }
                    this.$emit('input', false);
                    this.loading = false;
                    this.right = null;
                    this.showMessage(
                        'Personen werden zusammengeführt.'
                    );
                }).catch(error => {
                    if (error) {
                        this.extractErrorMessages(error);
                    }
                    this.loading = false;
                });
            }
        }

        fillName(name) {
            this.$set(this.merged, 'lastName', name);
        }

        fillFirstName(firstName) {
            this.$set(this.merged, 'firstName', firstName);
        }

        fillBirthday(birthday) {
            this.$set(this.merged, 'birthday', birthday);
        }

        fillGender(gender) {
            this.$set(this.merged, 'gender', gender);
        }

        fillMerged() {
            this.fillName(this.merged_name);
            this.fillFirstName(this.merged_first_name);
            this.fillBirthday(this.merged_birthday);
            this.fillGender(this.merged_gender);
        }

        @Watch('left')
        onLeftChange() {
            this.fillMerged();
        }

        @Watch('right')
        onRightChange() {
            this.fillMerged();
        }

        gender: Object[] = [
            {value: '', text: 'unbekannt'},
            {value: 'MALE', text: 'männlich'},
            {value: 'FEMALE', text: 'weiblich'}
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

        get merged_gender() {
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
            return this.left ? this.left.lastName : '';
        }

        get left_first_name() {
            return this.left ? this.left.firstName : '';
        }

        get left_birthday() {
            return this.left && this.left.birthday ? this.left.birthday : '';
        }

        get left_sex() {
            return this.left && this.left.gender ? this.left.gender : '';
        }

        get right_name() {
            return this.right ? this.right.lastName : '';
        }

        get right_first_name() {
            return this.right ? this.right.firstName : '';
        }

        get right_birthday() {
            return this.right && this.right.birthday ? this.right.birthday : '';
        }

        get right_sex() {
            return this.right && this.right.gender ? this.right.gender : '';
        }
    }
</script>
