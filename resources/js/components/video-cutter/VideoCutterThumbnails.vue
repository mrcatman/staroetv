<template>
    <canvas ref="canvas" style="position: fixed;visibility: hidden"></canvas>
    <video muted ref="video_thumbnails" style="position: fixed; visibility: hidden">
        <source :src="cut.download_url">
    </video>
    <div @contextmenu.prevent="() => {}" @mousedown="startRightMouseSeeking" @mouseup="stopRightMouseSeeking" class="video-cutter__thumbnails__container">
        <div class="video-cutter__thumbnails__seek"></div>

        <div  class="video-cutter__thumbnails" v-bind="containerProps" @mousemove.prevent="moveDrag" @mousedown.prevent="startDragging" @mouseup="stopDragging">
            <div v-bind="wrapperProps" class="video-cutter__thumbnails__wrapper">
                <div class="video-cutter__thumbnails__item" v-for="item in list" :key="item.index" :data-index="item.index" :style="{visibility: item.data.time >= 0  && item.data.index / framesInSecond < framesCount ? 'visible' : 'hidden', width: `${itemWidth}px`, backgroundImage: item.data?.thumbnail ? `url(${item.data.thumbnail})` : ''}">
                    <span class="video-cutter__thumbnails__item__time">{{formatTime(item.data.time)}}</span>
                </div>
                <div v-for="(item, index) in cutResults" :style="getResultStyle(item, index)" class="video-cutter__thumbnails__result-indicator">

                </div>
            </div>
        </div>
    </div>

</template>
<style lang="scss">
.video-cutter {
    &__thumbnails {
        height: calc(75px + 1.5em);
        &__container {
            position: relative;
        }
        &__wrapper {
            position: relative;
        }
        &__seek {
            position: absolute;
            z-index: 2;
            top: 0;
            left: calc(50% - 2px);
            width: 4px;
            height: 100%;
            background: var(--primary);
        }
        &__item {
            height: 75px;
            position: relative;
            z-index: 1;
            background-position: center;
            background-size: cover;
            cursor: pointer;
            &:hover {
                opacity: .75;
            }
            &__time {
                position: absolute;
                bottom: -1.25em;
                left: 0;
                width: 100%;
                text-align: center;
            }
        }
        &__result-indicator {
            position: absolute;
            top: 0;
            height: 100%;
            opacity: .4;
        }
    }
}
</style>
<script lang="ts" setup>
import { computed, ref, watch, useTemplateRef, onMounted } from 'vue'
import { useVirtualList } from '@vueuse/core'
import { type CutResult } from '../VideoCutter.vue'

const props = defineProps<{
    cut: Models.VideoCut;
    cutResults: CutResult[];
    fps: number;
}>();

const currentFrame = defineModel<number>('frame');

const emit = defineEmits<{
    (e: 'seek', index: number): void
}>();

const framesInSecond = 2;
const framesCount = Math.round(props.cut.frames / props.fps) * framesInSecond;
const halfItemsCount = computed(() => {
    return Math.round(visibleItemsCount / 2)
});
const currentThumbnailIndex = computed(() => {
    return Math.round(currentFrame.value / props.fps) * framesInSecond;
});
const itemWidth = 115;
const visibleItemsCount = 6;

const thumbnails = ref(Array.from(Array.from({ length: framesCount + visibleItemsCount }).keys()).map((index) => {
    return {
        index: index - halfItemsCount.value,
        time: (index  - halfItemsCount.value) / framesInSecond,
        thumbnail: null,
    }
}));

const { list, containerProps, wrapperProps, scrollTo  } = useVirtualList(
    thumbnails,
    {
        itemWidth,
    },
)

let timeout: any;

const canvasRef = useTemplateRef<HTMLCanvasElement>('canvas');
const videoThumbnailsRef = useTemplateRef<typeof HTMLVideoElement>('video_thumbnails');
let ctx: CanvasRenderingContext2D;

watch(() => currentFrame.value, (frame) => {
    if (mouseDown) {
        return;
    }
    containerProps.ref.value.scrollLeft = frame / props.fps * itemWidth * framesInSecond;
    remakeThumbnails();
})

const seekVideo = (time: number) => {
    videoThumbnailsRef.value.currentTime = time;
    return new Promise<void>((resolve, reject) => {
        const onSeeked = () => {
            removeEventListener('seeked', onSeeked);
            resolve();
        }
         videoThumbnailsRef.value.addEventListener('seeked', onSeeked);
    })
}

const makeThumbnails = async () => {
    for (let i = currentThumbnailIndex.value - halfItemsCount.value; i < currentThumbnailIndex.value + halfItemsCount.value + visibleItemsCount; i++) {
        if (i < 0 || thumbnails.value[i].thumbnail || thumbnails.value[i].time < 0) {
            continue;
        }

        await seekVideo(thumbnails.value[i].time);
        canvasRef.value.width = videoThumbnailsRef.value.videoWidth;
        canvasRef.value.height = videoThumbnailsRef.value.videoHeight;

        ctx.drawImage(videoThumbnailsRef.value, 0, 0, canvasRef.value.width, canvasRef.value.height);

        thumbnails.value[i].thumbnail = canvasRef.value.toDataURL('image/jpeg', 0.8);

    }
}
onMounted(() => {
    const onLoaded = () => {
        videoThumbnailsRef.value.removeEventListener('seeked', onLoaded);
        makeThumbnails();
    }
    videoThumbnailsRef.value.addEventListener('seeked', onLoaded);
    videoThumbnailsRef.value.currentTime = 1;
    ctx = canvasRef.value.getContext('2d');
})

const remakeThumbnails = () => {
    clearTimeout(timeout);
    timeout = setTimeout(makeThumbnails, 500);
}

let mouseDown = false;
let startX = 0;
let scrollLeft = 0;

const startDragging = (e) => {
    if (e.button === 2) {
        return;
    }
    mouseDown = true;
    startX = e.pageX - containerProps.ref.value.offsetLeft;
    scrollLeft = containerProps.ref.value.scrollLeft;
}

const stopDragging = (e) => {
    mouseDown = false;

    const x = e.pageX - containerProps.ref.value.offsetLeft;
    const scroll = x - startX;

    if (Math.abs(scroll) < 5) {
        const item = e.target.closest('.video-cutter__thumbnails__item');
        if (!item) {
            return;
        }

        const index = parseInt(item.dataset.index);

        scrollTo(index);
        emit('seek', thumbnails.value[index].time);
        currentFrame.value = Math.round(thumbnails.value[index].time * props.fps);
    }
}

const moveDrag = (e) => {
    if (!mouseDown) {
        return;
    }

    const x = e.pageX - containerProps.ref.value.offsetLeft;
    const scroll = x - startX;

    containerProps.ref.value.scrollLeft = scrollLeft - scroll;
    const percent = containerProps.ref.value.scrollLeft / (containerProps.ref.value.scrollWidth - (itemWidth * visibleItemsCount));
    const time = percent * framesCount / framesInSecond;
    emit('seek', time);
    currentFrame.value = Math.round(time * props.fps);

    remakeThumbnails();
}

let rightMouseSeekInterval: any;
const startRightMouseSeeking = (e) => {
    if (e.button !== 2) {
        return;
    }
    const rect = containerProps.ref.value.getBoundingClientRect();
    const speed = (e.clientX - rect.left) / rect.width - .5;
    clearInterval(rightMouseSeekInterval);
    rightMouseSeekInterval = setInterval(() => {
        currentFrame.value += speed > 0 ? (currentFrame.value < framesCount * props.fps ? 1 : 0) : (currentFrame.value > 0 ? -1 : -0);
        emit('seek', currentFrame.value / props.fps);
    }, (.5 - Math.abs(speed)) * 100);
}

const stopRightMouseSeeking = (e) => {
    if (e.button !== 2) {
        return;
    }
    clearInterval(rightMouseSeekInterval);
}

const formatTime = (time: number) => {
    return new Date(time * 1000).toISOString().substring(14, 21);
}

const getResultStyle = (item: CutResult, index: number) => {
    const marginLeft = parseInt(wrapperProps.value.style.marginLeft!.replace('px', ''));
    return {
        left: `${item.start / props.fps * itemWidth * framesInSecond + (itemWidth * halfItemsCount.value) - marginLeft}px`,
        width: `${(item.end - item.start) / props.fps * itemWidth * framesInSecond}px`,
        backgroundColor: index % 2 === 0 ? 'var(--primary)' : 'var(--bg-darkest)',
    }
}
</script>
