<template>
    <div class="editor">
        <picture-uploader ref="picture-uploader" @change="addPicture" style="display: none" />
        <div class="editor__buttons">
            <button
                type="button" class="editor__button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'editor__button--active': editor?.isActive('bold') }"
                title="Жирный"
            >
                <strong>B</strong>
            </button>
            <button
                type="button" class="editor__button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'editor__button--active': editor?.isActive('italic') }"
                title="Курсив"
            >
                <em>I</em>
            </button>
            <button
                type="button" class="editor__button"
                @click="editor.chain().focus().toggleStrike().run()"
                :class="{ 'editor__button--active': editor?.isActive('strike') }"
                title="Перечеркнутый"
            >
                <s>S</s>
            </button>

            <span class="editor__divider"></span>

            <button
                v-for="i in 5"
                type="button" class="editor__button"
                @click="editor.chain().focus().toggleHeading({ level: i }).run()"
                :class="{ 'editor__button--active': editor?.isActive('heading', { level: i }) }"
                :title="`Заголовок ${i}`"
            >
                H{{i}}
            </button>

            <span class="editor__divider"></span>

            <button
                type="button" class="editor__button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'editor__button--active': editor?.isActive('bulletList') }"
                title="Список"
            >
                <i class="fa fa-list-ul"></i>
            </button>
            <button
                type="button" class="editor__button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ 'editor__button--active': editor?.isActive('orderedList') }"
                title="Числовой список"
            >
                <i class="fa fa-list-ol"></i>
            </button>

            <span class="editor__divider"></span>

            <button
                type="button" class="editor__button"
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ 'editor__button--active': editor?.isActive('blockquote') }"
                title="Цитата"
            >
                "
            </button>
            <button
                type="button" class="editor__button"
                @click="editor.chain().focus().setHorizontalRule().run()"
                title="Разделитель"
            >
                ─
            </button>

            <span class="editor__divider"></span>

            <button
                type="button" class="editor__button"
                @click="pictureUploader?.loadFile()"
                title="Загрузить картинку"
            >
                <i class="fa fa-upload"></i>
            </button>
            <button
                type="button" class="editor__button"
                @click="pictureUploader?.loadFromURL()"
                title="Загрузить картинку по URL"
            >
                <i class="fa fa-image"></i>
            </button>
            <button
                type="button" class="editor__button"
                @click="addVideo()"
                title="Добавить видео"
            >
                <i class="fa fa-video"></i>
            </button>
            <response class="editor__error" :light="true" v-if="pictureUploader?.error" :data="{status: 0, text: pictureUploader.error}" />
        </div>
        <div class="editor__content">
            <editor-content :editor="editor" />
        </div>
    </div>

</template>

<script setup lang="ts">
import { onMounted, onUnmounted, useTemplateRef } from "vue";
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import TextAlign from '@tiptap/extension-text-align'
import { TextStyleKit } from '@tiptap/extension-text-style'
import Typography from '@tiptap/extension-typography'
import { parseVideoUrl, VideoEmbed } from './tiptap-editor/video-embed-node'

import PictureUploader from "@/components/PictureUploader.vue";

const props = defineProps<{
    name: string,
    content: string
}>();

const editor = useEditor({
    content: props.content,
    extensions: [
        StarterKit,
        Image.configure({
            resize: {
                enabled: true,
                alwaysPreserveAspectRatio: true,
            },
        }),
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        TextStyleKit,
        Typography.configure({}),
        VideoEmbed,
    ]
})

const pictureUploader = useTemplateRef<typeof PictureUploader>('picture-uploader');
const addPicture = (picture: Models.Picture) => {
    if (!picture) {
        return;
    }
    editor.value.chain().focus().setImage({ src: picture.url }).run()
}


const addVideo = () => {
    const url = prompt('Ссылка или код для вставки видео');
    if (!url) return;

    const src = parseVideoUrl(url);
    if (!src) return;

    editor.value.chain().focus().insertContent({
        type: 'video-embed',
        attrs: {
            src
        },
    }).run();
}

onMounted(() => {
    window.activeEditors[props.name] = editor.value;
});
onUnmounted(() => {
    delete window.activeEditors[props.name];
})
</script>

<style scoped lang="scss">
.editor {
    margin: 1em 0;
    &__buttons {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: .5em;
    }
    &__button {
        padding: .5em 1em;
        border: 1px solid var(--border-color);
        background: var(--inputs-color);
        box-shadow: var(--input-box-shadow);
        border-radius: var(--border-radius-small);
        color: inherit;
        font: inherit;
        cursor: pointer;
        transition: all .2s;
        &:hover {
            background: var(--bg-darker);
        }

        &--active {
            background: var(--bg-darker-2);
            color: var(--primary);
        }
    }
    &__content {
        max-height: 30em;
        overflow: auto;
        padding: .75em;
        background: var(--inputs-color);
        box-shadow: var(--input-box-shadow);
        border: 1px solid var(--border-color);
        font-family: var(--font-texts);
        * {
            outline: none;
        }
        img {
            max-width: 100%;
        }
        iframe {
            pointer-events: none;
        }
    }
    &__divider {
        margin: 0 .5em;
        width: 1px;
        background: var(--border-color);
        align-self: stretch;
    }
    &__error {
        margin-left: var(--col-margin);
    }
}
</style>
