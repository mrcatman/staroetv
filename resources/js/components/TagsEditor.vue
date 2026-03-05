<template>
    <div class="tags-editor">
        <input type="hidden" name="tags" :value="JSON.stringify(selectedTags)" />
        <vue-tags-input
            v-model="tag"
            :tags="selectedTags"
            :autocomplete-items="filteredItems"
            @tags-changed="newTags => selectedTags = newTags"
        />
    </div>
</template>
<style lang="scss">
.tags-editor {
    .vue-tags-input {
        max-width: unset !important;
        width: 100%;
        background: var(--inputs-color);
    }
    .ti-autocomplete {
        background: var(--inputs-color);
        border: 1px solid var(--border-color);
        box-shadow: var(--input-box-shadow);
        top: 100%;
        left: 0;
        max-height: 10em;
        overflow: auto;
    }
    .ti-selected-item {
        background-color: var(--primary) !important;
    }
    .ti-item>div {
        padding: .5em!important;
    }

    .ti-new-tag-input {
        font: inherit;
        background: none;
    }

    .ti-input {
        background: none !important;
        border: none !important;
        padding: 0 !important;
        font: inherit;
        margin: -.35em;

        &:focus {
            border-bottom: 3px solid var(--primary);
        }
    }

    .ti-new-tag-input-wrapper {
        background: none;
        padding: 0 !important;
        margin: 2px;
        font-size: 1em !important;
    }

    .ti-tag {
        background: var(--primary) !important;
        font-size: 1em !important;
    }
}
</style>
<script lang="ts" setup>
import { computed, ref } from "vue";
import vueTagsInput from "@wslyhbb/vue3-tags-input";

interface Tag {
    id: string,
    name: string
}

const props = defineProps<{
    tags: Tag[],
    allTags: Tag[]
}>();

const tag = ref<string>('');
const selectedTags = ref(props.tags.map(tag => {
    return {
        id: tag.id,
        text: tag.name
    }
}));
const autocompleteItems = ref(props.allTags.map(tag => {
    return {
        id: tag.id,
        text: tag.name
    }
}));

const filteredItems = computed(() =>{
    if (tag.value.length === 0) {
        return autocompleteItems.value;
    }
    return autocompleteItems.value.filter(i => {
        return i.text.toLowerCase().indexOf(tag.value.toLowerCase()) !== -1;
    });
});

</script>
