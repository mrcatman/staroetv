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
        width: 100%;
        .vue-tags-input {
            max-width: unset!important;
            width: 100%;
        }
        .ti-new-tag-input {
            font: inherit;
            background: none;
        }
        .ti-input {
            background: var(--bg-darker);
            border: 1px solid var(--border-color);
            &:focus {
                border-bottom: 3px solid var(--primary);
            }
        }
        .ti-new-tag-input-wrapper {
            background: none;
        }
        .ti-tag {
            background: var(--primary)!important;
        }
    }
</style>
<script>
    import vueTagsInput from '@johmun/vue-tags-input';
    export default {
        computed: {
            filteredItems() {
                if (this.tag.length === 0) {
                    return this.autocompleteItems;
                }
                return this.autocompleteItems.filter(i => {
                    return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
                });
            },
        },
        data() {
            return {
                tag: '',
                selectedTags: this.tags.map(tag => {
                    return {
                        id: tag.id,
                        text: tag.name
                    }
                }),

                autocompleteItems: this.allTags.map(tag => {
                    return {
                        id: tag.id,
                        text: tag.name
                    }
                }),
            }
        },
        components: {
            vueTagsInput
        },
        props: {
            tags: {
                type: Array,
                required: true
            },
            allTags: {
                type: Array,
                required: true
            }
        }
    }
</script>
