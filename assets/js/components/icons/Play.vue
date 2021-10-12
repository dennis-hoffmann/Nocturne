<template>
  <span :aria-hidden="decorative"
        :aria-label="title"
        class="material-design-icon play-icon"
        role="img"
  >
    <svg :fill="fillColor"
         class="material-design-icon__svg"
         :width="size"
         :height="size"
         viewBox="0 0 24 24">
      <path d="M8,5.14V19.14L19,12.14L8,5.14Z">
        <title v-if="!decorative">{{ title }}</title>
      </path>
    </svg>
  </span>
</template>

<script>
    export default {
        name: "PlayIcon",
        props: {
            title: {
                type: String,
                default: "Play icon"
            },
            decorative: {
                type: Boolean,
                default: false
            },
            fillColor: {
                type: String,
                default: "currentColor"
            },
            size: {
                type: Number,
                default: 24
            }
        }
    }
</script>
