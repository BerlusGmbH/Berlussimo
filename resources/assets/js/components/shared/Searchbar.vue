<template>
    <b-entity-select @input="select"
                     :value="selected"
                     hide-details
                     prepend-icon="search"
                     append-icon=""
                     :entities="['objekt', 'person', 'haus', 'einheit', 'partner']"
                     solo-inverted
    >
    </b-entity-select>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import Select from "../common/EntitySelect.vue"

    @Component({components: {'b-entity-select': Select}})
    export default class Searchbar extends Vue {

        selected: Array<Object> = [];

        select(entity) {
            let url = new URL(entity.getDetailUrl());
            if (this.$router && this.$router.resolve(url.pathname + url.search).route.name) {
                this.selected = [];
                this.$router.push(url.pathname + url.search);
            } else {
                window.location.assign(entity.getDetailUrl());
            }
        }
    }
</script>