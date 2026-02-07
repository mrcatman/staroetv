<template>
    <template v-if="record.is_radio">
        <div :data-id="record.id" class="radio-recording">
            <a target="_blank" :href="record.url" class="radio-recording__button">
                <i class="fa fa-play"></i>
            </a>
            <div class="radio-recording__texts">
                <component :is="disableLinks ? 'span' : 'a'" target="_blank" :href="record.url" class="radio-recording__title">
                    <slot name="title">{{record.title}}</slot>
                </component>
                <slot name="description"></slot>
                <div class="radio-recording__info">
                    <span class="radio-recording__date"><i class="fa fa-calendar"></i>{{record.created_at}}</span>
                    <span class="radio-recording__listens"><i class="fa fa-headphones-alt"></i>{{record.views}}</span>
                </div>
            </div>
        </div>
    </template>
    <template v-else>
        <div :data-id="record.id" class="record-item">
            <a target="_blank" :href="record.url" class="record-item__cover" :style="`background-image: url(${record.cover})`">
                <div v-if="record.formatted_duration" class="record-item__duration">{{record.formatted_duration}}</div>
            </a>

            <div class="record-item__texts">
                <component :is="disableLinks ? 'span' : 'a'" target="_blank" :href="record.url" class="record-item__title">
                    <slot name="title">{{record.title}}</slot>
                </component>
                <slot name="description"></slot>
                <div class="record-item__info">
                    <span class="record-item__date"><i class="fa fa-calendar"></i>{{record.created_at}}</span>
                    <span class="record-item__views"><i class="fa fa-eye"></i>{{record.views}}</span>
                </div>
            </div>
        </div>
    </template>

</template>
<script lang="ts" setup>
defineProps<{
    record: Models.Record,
    disableLinks?: boolean
}>();
</script>
