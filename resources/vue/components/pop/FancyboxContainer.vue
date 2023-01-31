<template>
    <transition name="fade">
        <div ref="modal" @keydown.esc="close_pop"  class="fancybox-container fancybox-is-modal fancybox-is-open" role="dialog" tabindex="-1"
             id="fancybox-container-6" style="transition-duration: 366ms;">
            <div class="fancybox-bg"></div>
            <div class="fancybox-inner">
                <div class="fancybox-infobar"><span data-fancybox-index="">1</span>&nbsp;/&nbsp;<span
                    data-fancybox-count="">1</span></div>
                <div class="fancybox-toolbar">
                    <button  data-fancybox-zoom="" class="fancybox-button fancybox-button--zoom" title="Zoom" disabled="">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                                d="M18.7 17.3l-3-3a5.9 5.9 0 0 0-.6-7.6 5.9 5.9 0 0 0-8.4 0 5.9 5.9 0 0 0 0 8.4 5.9 5.9 0 0 0 7.7.7l3 3a1 1 0 0 0 1.3 0c.4-.5.4-1 0-1.5zM8.1 13.8a4 4 0 0 1 0-5.7 4 4 0 0 1 5.7 0 4 4 0 0 1 0 5.7 4 4 0 0 1-5.7 0z"></path>
                        </svg>
                    </button>
                    <button  data-fancybox-play="" class="fancybox-button fancybox-button--play" title="Start slideshow"
                            style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M6.5 5.4v13.2l11-6.6z"></path>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path d="M8.33 5.75h2.2v12.5h-2.2V5.75zm5.15 0h2.2v12.5h-2.2V5.75z"></path>
                        </svg>
                    </button>
                    <button data-fancybox-thumbs="" class="fancybox-button fancybox-button--thumbs" title="Thumbnails"
                            style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                                d="M14.59 14.59h3.76v3.76h-3.76v-3.76zm-4.47 0h3.76v3.76h-3.76v-3.76zm-4.47 0h3.76v3.76H5.65v-3.76zm8.94-4.47h3.76v3.76h-3.76v-3.76zm-4.47 0h3.76v3.76h-3.76v-3.76zm-4.47 0h3.76v3.76H5.65v-3.76zm8.94-4.47h3.76v3.76h-3.76V5.65zm-4.47 0h3.76v3.76h-3.76V5.65zm-4.47 0h3.76v3.76H5.65V5.65z"></path>
                        </svg>
                    </button>
                    <button data-fancybox-close="" class="fancybox-button fancybox-button--close" title="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path
                                d="M12 10.6L6.6 5.2 5.2 6.6l5.4 5.4-5.4 5.4 1.4 1.4 5.4-5.4 5.4 5.4 1.4-1.4-5.4-5.4 5.4-5.4-1.4-1.4-5.4 5.4z"></path>
                        </svg>
                    </button>
                </div>
                <div class="fancybox-navigation">
                    <button data-fancybox-prev="" class="fancybox-button fancybox-button--arrow_left" title="Previous"
                            disabled="">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path d="M11.28 15.7l-1.34 1.37L5 12l4.94-5.07 1.34 1.38-2.68 2.72H19v1.94H8.6z"></path>
                            </svg>
                        </div>
                    </button>
                    <button data-fancybox-next="" class="fancybox-button fancybox-button--arrow_right" title="Next"
                            disabled="">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path
                                    d="M15.4 12.97l-2.68 2.72 1.34 1.38L19 12l-4.94-5.07-1.34 1.38 2.68 2.72H5v1.94z"></path>
                            </svg>
                        </div>
                    </button>
                </div>
                <div class="fancybox-stage">
                    <div class="fancybox-slide fancybox-slide--html fancybox-slide--current fancybox-slide--complete"
                         style="">

                        <slot></slot>

                    </div>
                </div>
                <div class="fancybox-caption fancybox-caption--separate">
                    <div class="fancybox-caption__body"></div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: "FancyboxContainer",
    computed: {
      active(){
          return this.$store.state.pop || null
      }
    },
    methods: {
        close_pop() {
            this.$store.commit('close_pop')
        },
    },
   async mounted() {
       await this.$nextTick(() => this.$refs.modal.focus())
    }
}
</script>

<style scoped>

</style>
