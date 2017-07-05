<template>
    <div :id="id" class="modal">
        <form>
            <div class="modal-content">
                <h4>
                    <i class="mdi mdi-account"></i
                    ><i style="margin-left: -10px" class="mdi mdi-call-merge"></i
                ><i style="margin-left: -10px" class="mdi mdi-account"></i>
                    Personen zusammenführen
                </h4>
                <div class="row">
                    <div class="col-xs-12">
                        <selector id="person_selector" :entities="['person']"></selector>
                    </div>
                </div>
                <div class="row center-xs">
                    <div class="col-xs-3">
                        <h5>Links</h5>
                    </div>
                    <div class="col-xs-1">
                    </div>
                    <div class="col-xs-4">
                        <h5>Zusammen</h5>
                    </div>
                    <div class="col-xs-1">
                    </div>
                    <div class="col-xs-3">
                        <h5>Rechts</h5>
                    </div>
                    <div class="input-field col-xs-3">
                        <input type="text" minlength="1" maxlength="255" id="name" name="name"
                               :value="person.name" disabled>
                        <label for="name">Nachname</label>
                    </div>
                    <div class="input-field col-xs-1">
                        <i @click="fillName(person.name)" class="mdi mdi-arrow-right"></i>
                    </div>
                    <div class="input-field col-xs-4">
                        <i class="mdi mdi-alphabetical prefix"></i>
                        <input type="text" minlength="1" maxlength="255" id="name" name="name"
                               v-model="merged.name" class="validate">
                        <label :class="{'active': merged.name}" for="name">Nachname</label>
                        <span class="error-block"></span>
                    </div>
                    <div class="input-field col-xs-1">
                        <i @click="fillName(right_name)" class="mdi mdi-arrow-left"></i>
                    </div>
                    <div class="input-field col-xs-3">
                        <input type="text" minlength="1" maxlength="255" id="name" name="name"
                               :value="right_name" disabled>
                        <label :class="{'active': right_name !== ''}" for="name">Nachname</label>
                    </div>
                    <div class="input-field col-xs-3">
                        <input type="text" minlength="1" maxlength="255" id="first_name" name="first_name"
                               :value="person.first_name" disabled>
                        <label for="first_name">Vorname</label>
                    </div>
                    <div class="input-field col-xs-1">
                        <i @click="fillFirstName(person.first_name)" class="mdi mdi-arrow-right"></i>
                    </div>
                    <div class="input-field col-xs-4">
                        <i class="mdi mdi-alphabetical prefix"></i>
                        <input type="text" minlength="1" maxlength="255" id="first_name" name="first_name"
                               v-model="merged.first_name" class="validate">
                        <label :class="{'active': merged.first_name}" for="first_name">Vorname</label>
                        <span class="error-block"></span>
                    </div>
                    <div class="input-field col-xs-1">
                        <i @click="fillFirstName(right_first_name)" class="mdi mdi-arrow-left"></i>
                    </div>
                    <div class="input-field col-xs-3">
                        <input type="text" minlength="1" maxlength="255" id="first_name" name="first_name"
                               :value="right_first_name" disabled>
                        <label :class="{'active': right_first_name !== ''}" for="first_name">Vorname</label>
                    </div>
                    <div class="input-field col-xs-3">
                        <input type="date" id="birthday" name="birthday"
                               :value="left_birthday" disabled>
                        <label class="active" for="birthday" data-error="">Geburtstag</label>
                    </div>
                    <div class="input-field col-xs-1">
                        <i @click="fillBirthday(left_birthday)" class="mdi mdi-arrow-right"></i>
                    </div>
                    <div class="input-field col-xs-4">
                        <i class="mdi mdi-cake prefix"></i>
                        <input type="date" id="birthday" name="birthday"
                               v-model="merged.birthday" class="validate">
                        <span :class="{'active': merged.birthday}" class="error-block"></span>
                        <label class="active" for="birthday" data-error="">Geburtstag</label>
                    </div>
                    <div class="input-field col-xs-1">
                        <i @click="fillBirthday(right_birthday)" class="mdi mdi-arrow-left"></i>
                    </div>
                    <div class="input-field col-xs-3">
                        <input type="date" id="birthday" name="birthday"
                               :value="right_birthday" disabled>
                        <label class="active" for="birthday" data-error="">Geburtstag</label>
                    </div>
                    <div class="input-field col-xs-3">
                        <input type="text" minlength="1" maxlength="255" id="name" name="name"
                               :value="person.sex" disabled>
                        <label for="name">Geschlecht</label>
                    </div>
                    <div class="input-field col-xs-1">
                        <i @click="fillSex(person.sex)" class="mdi mdi-arrow-right"></i>
                    </div>
                    <div class="input-field col-xs-4">
                        <i class="mdi mdi-alphabetical prefix"></i>
                        <input type="text" minlength="1" maxlength="255" id="name" name="name"
                               v-model="merged.sex" class="validate">
                        <label :class="{'active': merged.sex}" for="name">Geschlecht</label>
                        <span class="error-block"></span>
                    </div>
                    <div class="input-field col-xs-1">
                        <i @click="fillSex(right_sex)" class="mdi mdi-arrow-left"></i>
                    </div>
                    <div class="input-field col-xs-3">
                        <input type="text" minlength="1" maxlength="255" id="name" name="name"
                               :value="right_sex" disabled>
                        <label :class="{'active': right_sex}" for="name">Geschlecht</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button @click="onSubmit" class="modal-action btn waves-effect waves-light red" type="button">
                    Zusammenführen
                    <i class="mdi mdi-call-merge left"></i>
                </button>
                <a class="modal-close waves-effect btn-flat">Abbrechen</a>
            </div>
        </form>
    </div>
</template>

<script>
    import Selector from './Selector.vue';
    import {mapGetters} from 'vuex';

    const NAMESPACE = 'modules/person/merge';

    export default {
        mounted() {
            this.fillMerged();
        },
        methods: {
            onSubmit: function () {
                let vm = this;
                $.ajax('/api/v1/personen/' + this.person.id + '/merge/' + this.right.id,
                    {
                        data: this.merged
                    }
                ).done(function () {
                    Materialize.toast('Personen werden zusammengeführt.', 3000, 'rounded');
                    $('#' + vm.id).modal('close');
                }).fail(function (jqXHR, status, error) {
                    Materialize.toast(
                        'Personen können nicht zusammengeführt werden.\nFehler beim starten: ' +
                        error, 3000, 'rounded'
                    );
                });
            },
            fillName(name) {
                this.$set(this.merged, 'name', name);
            },
            fillFirstName(first_name) {
                this.$set(this.merged, 'first_name', first_name);
            },
            fillBirthday(birthday) {
                this.$set(this.merged, 'birthday', birthday);
            },
            fillSex(sex) {
                this.$set(this.merged, 'sex', sex);
            },
            fillMerged() {
                this.fillName(this.merged_name);
                this.fillFirstName(this.merged_first_name);
                this.fillBirthday(this.merged_birthday);
                this.fillSex(this.merged_sex);
            }
        },
        props: ['id', 'person'],
        data: function () {
            return {
                merged: {
                    name: '',
                    first_name: null,
                    birthday: null,
                    sex: null
                }
            }
        },
        watch: {
            right() {
                this.fillMerged();
            }
        },
        computed: {
            ...mapGetters(
                NAMESPACE + '/selector',
                {right: 'firstSelected'}
            ),
            left_birthday: function () {
                return this.person && this.person.birthday ? this.person.birthday.substring(0, 10) : '';
            },
            merged_name: function () {
                if (this.right_name && !this.person.name) {
                    return this.right_name;
                }
                if (!this.right_name && this.person.name) {
                    return this.person.name;
                }
                if (this.right_name === this.person.name) {
                    return this.person.name;
                }
                return '';
            },
            merged_first_name: function () {
                if (this.right_first_name && !this.person.first_name) {
                    return this.right_first_name;
                }
                if (!this.right_first_name && this.person.first_name) {
                    return this.person.first_name;
                }
                if (this.right_first_name === this.person.first_name) {
                    return this.person.first_name;
                }
                return '';
            },
            merged_birthday: function () {
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
            },
            merged_sex: function () {
                if (this.right_sex && !this.person.sex) {
                    return this.right_sex;
                }
                if (!this.right_sex && this.person.sex) {
                    return this.person.sex;
                }
                if (this.right_sex === this.person.sex) {
                    return this.person.sex;
                }
                return '';
            },
            right_name: function () {
                return this.right ? this.right.name : '';
            },
            right_first_name: function () {
                return this.right ? this.right.first_name : '';
            },
            right_birthday: function () {
                return this.right && this.right.birthday ? this.right.birthday.substring(0, 10) : '';
            },
            right_sex: function () {
                return this.right && this.right.sex ? this.right.sex : '';
            },
        },
        components: {
            selector: Selector
        }
    }
</script>