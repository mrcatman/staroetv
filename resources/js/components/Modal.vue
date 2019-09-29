<template>
    <div>
        <div ref="sizeTester" class="size-tester" v-if="testingSizes">
            <slot></slot>
        </div>
        <div v-if="visible">
            <vue-draggable-resizable class="modal-window modal-window--vue" :x="x" :y="y" :w="width" :h="height" @dragging="onDrag" @resizing="onResize">
                <div class="modal-window__inner" ref="inner">
                    <div class="form__preloader" v-show="loading"></div>
                    <div class="modal-window__top">
                        <div class="modal-window__title">{{title}}</div>
                        <div @click="hide()" class="modal-window__close">x</div>
                    </div>
                    <div class="modal-window__content">
                        <div class="modal-window__content__inner">
                            <slot></slot>
                        </div>
                    </div>
                </div>
            </vue-draggable-resizable>
        </div>
    </div>
</template>
<style lang="scss">
    .size-tester {
        display: inline-block;
    }
    .vdr {
        border: none;
    }
    .handle {
        opacity: 0;
    }
</style>
<script>
    import VueDraggableResizable from 'vue-draggable-resizable'
    import 'vue-draggable-resizable/dist/VueDraggableResizable.css'

    export default {
        props: ['title', 'loading'],
        data () {
            return {
                testingSizes: false,
                visible: false,
                width: 640,
                height: 480,
                x: 0,
                y: 0
            }
        },
        watch: {
            loading() {
                this.setSize();
            }
        },
        mounted() {
            //this.setSize();
        },
        methods: {
            hide() {
                this.visible = false;
            },
            async show() {
                await this.setSize();
                this.visible = true;
            },
            setSize() {
                return new Promise(resolve => {
                    this.testingSizes = true;
                    this.$nextTick(() => {
                        let width = this.$refs.sizeTester.offsetWidth;
                        let height = this.$refs.sizeTester.offsetHeight;
                        this.width = width + 40;
                        this.height = height + 70;
                        this.testingSizes = false;
                        this.x = (window.innerWidth - this.width) / 2;
                        this.y = (window.innerHeight - this.height) / 2;
                        this.visible = true;
                        resolve();
                    });
                })
            },
            onResize(x, y, width, height) {
                this.x = x;
                this.y = y;

            },
            onDrag(x, y) {
                this.x = x
                this.y = y
            }
        }
    }
</script>