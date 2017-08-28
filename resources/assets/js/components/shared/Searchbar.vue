<template>
    <app-entity-select @input="select" hide-details prepend-icon="search" append-icon=""
                       :entities="['person', 'partner', 'objekt', 'haus', 'einheit']">
    </app-entity-select>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";
    import Select from "../common/EntitySelect.vue"

    @Component({components: {'app-entity-select': Select}})
    export default class Searchbar extends Vue {
        select(entity) {
            window.location.href = entity.getDetailUrl();
        }
    }
</script>