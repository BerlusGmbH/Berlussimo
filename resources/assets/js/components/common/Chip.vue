<template>
    <div v-once tabindex="0" class="chip">
        <i class="chip-prefix mdi" :class="entity.icon"></i>
        {{String(entity)}}
        <i class="chip-close material-icons" @click="remove">close</i>
    </div>
</template>

<script>
    export default {
        props: {
            'entity': Object
        },
        data: function () {
            return {}
        },
        methods: {
            remove() {
                this.$emit('remove');
            }
        }
    }
</script>

<style>
    .chip-close {
        cursor: pointer;
        float: right;
        font-size: 16px;
        line-height: 32px;
        padding-left: 8px;
    }

    .chip-prefix {
        float: left;
        font-size: 16px;
        line-height: 32px;
        padding-right: 8px;
    }
</style>