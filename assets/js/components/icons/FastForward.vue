<template>
  <span :aria-hidden="decorative"
        :aria-label="title"
        class="material-design-icon fast-forward-icon"
        role="img"
  >
    <svg :fill="fillColor"
         class="material-design-icon__svg"
         :width="size"
         :height="size"
         viewBox="0 0 24 24">
      <path d="M13,6V18L21.5,12M4,18L12.5,12L4,6V18Z">
        <title v-if="!decorative">{{ title }}</title>
      </path>
    </svg>
  </span>
</template>

<script>
    export default {
        name: "FastForwardIcon",
        props: {
            title: {
                type: String,
                default: "Fast Forward icon"
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
